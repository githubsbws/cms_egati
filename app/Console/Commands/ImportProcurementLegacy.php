<?php

namespace App\Console\Commands;

use App\Models\ProcurementAgency;
use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use App\Models\ProcurementWorkType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time import of the customer's old procurement database (a pg_dump .sql file with INSERT statements).
 * Old tables map to procurement_records by type: plan, midprice (price), anounceprocure (notice),
 * winnerprocure (winner_in), winneruser (winner_out). Old admin accounts and the log tables are not imported.
 * Safe to run again: records are matched on (type, legacy_id).
 */
class ImportProcurementLegacy extends Command
{
    protected $signature = 'procurement:import-legacy {file : Path to the .sql dump} {--dry-run : Read and convert everything, then roll back}';

    protected $description = 'Import the old procurement data (plans, prices, notices, winners) from a SQL dump';

    private const LEGACY_SITE = 'https://procure.egati.co.th/';

    /** @var array<string, array<int, string>> */
    private array $lookups = [];

    /** @var array<string, array<int, int|null>> */
    private array $ids = [];

    /** @var array<string, int> */
    private array $stats = [];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }
        $tables = $this->parseInserts((string) file_get_contents($path));

        DB::beginTransaction();
        try {
            $this->importLookups($tables);
            $this->importRecords($tables);
            $this->option('dry-run') ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        foreach ($this->stats as $name => $count) {
            $this->line(sprintf('%-34s %d', $name, $count));
        }
        $this->info($this->option('dry-run') ? 'Dry run finished, nothing was saved.' : 'Import finished.');

        return self::SUCCESS;
    }

    /**
     * Read every INSERT INTO public.<table> (...) VALUES (...); statement.
     *
     * @return array<string, list<array<string, string|null>>>
     */
    private function parseInserts(string $sql): array
    {
        $sql = str_replace("\r", '', $sql);
        $tables = [];
        preg_match_all('/^INSERT INTO public\.(\w+) \(([^)]*)\) VALUES \(/m', $sql, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => [$whole, $offset]) {
            $columns = array_map(fn (string $c): string => trim($c, ' "'), explode(',', $matches[2][$i][0]));
            $pos = $offset + strlen($whole);
            $values = $this->readValues($sql, $pos);
            $tables[$matches[1][$i][0]][] = array_combine($columns, array_slice(array_pad($values, count($columns), null), 0, count($columns)));
        }

        return $tables;
    }

    /**
     * @return list<string|null>
     */
    private function readValues(string $sql, int $pos): array
    {
        $values = [];
        $length = strlen($sql);
        while ($pos < $length) {
            while ($pos < $length && ctype_space($sql[$pos])) {
                $pos++;
            }
            if ($sql[$pos] === "'") {
                $text = '';
                $pos++;
                while ($pos < $length) {
                    if ($sql[$pos] === "'") {
                        if (($sql[$pos + 1] ?? '') === "'") {
                            $text .= "'";
                            $pos += 2;

                            continue;
                        }
                        $pos++;
                        break;
                    }
                    $text .= $sql[$pos++];
                }
                $values[] = $text;
            } else {
                $start = $pos;
                while ($pos < $length && $sql[$pos] !== ',' && $sql[$pos] !== ')') {
                    $pos++;
                }
                $token = trim(substr($sql, $start, $pos - $start));
                $values[] = strtoupper($token) === 'NULL' ? null : $token;
            }
            while ($pos < $length && ctype_space($sql[$pos])) {
                $pos++;
            }
            if (($sql[$pos] ?? ')') === ')') {
                break;
            }
            $pos++;
        }

        return $values;
    }

    /**
     * @param  array<string, list<array<string, string|null>>>  $tables
     */
    private function importLookups(array $tables): void
    {
        $models = [
            'methodagency' => ProcurementAgency::class,
            'methodprocure' => ProcurementWorkType::class,
            'methodsupply' => ProcurementMethod::class,
        ];
        foreach ($models as $table => $model) {
            foreach ($tables[$table] ?? [] as $row) {
                $name = trim((string) $row['name']);
                // "-" in the old data means "no value", so it becomes an empty link
                $this->ids[$table][(int) $row['id']] = ($name === '' || $name === '-') ? null : $model::firstOrCreate(['name' => $name], ['sort_order' => (int) $row['id']])->id;
            }
            $this->stats[$table] = count($tables[$table] ?? []);
        }
        foreach (['yanounceprocure', 'yplan', 'ymidprice', 'ywinnerprocure', 'ywinneruser'] as $table) {
            foreach ($tables[$table] ?? [] as $row) {
                $this->lookups[$table][(int) $row['id']] = trim((string) $row['name']);
            }
        }
    }

    /**
     * @param  array<string, list<array<string, string|null>>>  $tables
     */
    private function importRecords(array $tables): void
    {
        $this->importType($tables, 'plan', 'plan', 'yplan', fn (array $r): array => [
            'title' => $r['projectName'],
            'amount' => $r['price'],
            'attachments' => [['attachment', $r['file']], ['link', $r['changlink']]],
        ]);
        $this->importType($tables, 'midprice', 'price', 'ymidprice', fn (array $r): array => [
            'title' => $r['name'],
            'agency' => $r['agency_id'],
            'method' => $r['supply_id'],
            'date' => $r['date'],
            'attachments' => [['attachment', $r['file']]],
        ]);
        $this->importType($tables, 'anounceprocure', 'notice', 'yanounceprocure', fn (array $r): array => [
            'title' => $r['projectName'],
            'agency' => $r['agency_id'],
            'work_type' => $r['cateprocure_id'],
            'method' => $r['methodSupply_id'],
            'date' => $r['date'],
            'amount' => $r['price'],
            'attachments' => [['middle_price', $r['file']], ['draft_announcement', $r['draftano']], ['announcement', $r['file2']]],
        ]);
        foreach (['winnerprocure' => ['winner_in', 'ywinnerprocure'], 'winneruser' => ['winner_out', 'ywinneruser']] as $table => [$type, $years]) {
            $this->importType($tables, $table, $type, $years, fn (array $r): array => [
                'title' => $r['listname'],
                'vendor' => $r['name'],
                'tax_id' => $r['codeId'],
                'agency' => $r['agency_id'],
                'method' => $r['methodSupply_id'],
                'date' => $r['date'],
                'amount' => $r['price'],
                'reference' => $r['refnumber'],
                'reason' => $r['reason'],
                'attachments' => [['attachment', $r['file']]],
            ]);
        }
    }

    /**
     * @param  array<string, list<array<string, string|null>>>  $tables
     * @param  callable(array<string, string|null>): array<string, mixed>  $map
     */
    private function importType(array $tables, string $table, string $type, string $yearTable, callable $map): void
    {
        $bad = 0;
        foreach ($tables[$table] ?? [] as $row) {
            $d = $map($row);
            $year = (int) ($this->lookups[$yearTable][(int) $row['year_id']] ?? 0);
            if ($year === 0) {
                $year = (int) substr((string) ($row['date'] ?? $row['dateCrate']), 0, 4);
            }
            $amount = $this->parseAmount($d['amount'] ?? null);
            if (($d['amount'] ?? '') !== '' && $d['amount'] !== null && $amount === null) {
                $bad++;
            }
            $created = $this->parseDateTime($row['dateCrate'] ?? null);

            $record = ProcurementRecord::withTrashed()->firstOrNew(['type' => $type, 'legacy_id' => (int) $row['id']]);
            $record->fill([
                'fiscal_year' => $year,
                'title' => trim((string) $d['title']),
                'vendor_name' => $this->clean($d['vendor'] ?? null),
                'vendor_tax_id' => $this->clean($d['tax_id'] ?? null),
                'agency_id' => $this->ids['methodagency'][(int) ($d['agency'] ?? 0)] ?? null,
                'work_type_id' => $this->ids['methodprocure'][(int) ($d['work_type'] ?? 0)] ?? null,
                'method_id' => $this->ids['methodsupply'][(int) ($d['method'] ?? 0)] ?? null,
                'amount' => $amount,
                'published_on' => $this->parseDate($d['date'] ?? null),
                'reference_no' => $this->clean($d['reference'] ?? null),
                'reason' => $this->clean($d['reason'] ?? null),
                'status' => 'published',
                'legacy_user' => $this->clean($row['userCreate'] ?? null),
            ]);
            if ($created) {
                $record->created_at = $created;
                $record->updated_at = $created;
            }
            $record->save();

            $record->attachments()->delete();
            foreach (array_values(array_filter($d['attachments'], fn (array $a): bool => trim((string) $a[1]) !== '')) as $i => [$kind, $value]) {
                $value = trim((string) $value);
                $record->attachments()->create([
                    'kind' => $kind,
                    'external_url' => $this->toUrl($value),
                    'sort_order' => $i,
                ]);
            }
        }
        $this->stats[$type.' (records)'] = count($tables[$table] ?? []);
        if ($bad > 0) {
            $this->stats[$type.' (amount not a number, left empty)'] = $bad;
        }
    }

    /**
     * Old files lived on the old website; keep a working link until the real files are uploaded.
     */
    private function toUrl(string $value): string
    {
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return self::LEGACY_SITE.implode('/', array_map('rawurlencode', explode('/', ltrim($value, '/'))));
    }

    private function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseAmount(?string $value): ?string
    {
        // Takes the first number in the text, so "3,370.50 <unit word>" still works
        if (! preg_match('/\d[\d,]*(?:\.\d+)?/', (string) $value, $m)) {
            return null;
        }

        return number_format((float) str_replace(',', '', $m[0]), 2, '.', '');
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m) || (int) $m[1] < 1990 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return substr($value, 0, 10);
    }

    private function parseDateTime(?string $value): ?string
    {
        $date = $this->parseDate($value);

        return $date ? substr(trim((string) $value), 0, 19) : null;
    }
}
