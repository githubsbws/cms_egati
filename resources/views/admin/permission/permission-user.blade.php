@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.permis_sub.admin_list'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.permis_sub.admin_list') }}</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <div class="mb-2 d-flex justify-content-end">
                <input
                    type="text"
                    id="adminUserSearch"
                    class="form-control"
                    style="max-width: 260px;"
                    placeholder="{{ __('admin/common.search_placeholder') }}">
            </div>

            <div id="adminUserTable"></div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ปุ่มยกเลิกสิทธิ์ของแต่ละแถว — ยืนยันแล้ว submit form ไป route cancel
    function cancelPermission(userId, fullname) {
        if (! confirm(@json(__('admin/permission.confirm_cancel_permission_message')) + '\n' + fullname)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url('admin/permission/user') }}/' + userId + '/cancel';
        form.innerHTML = '@csrf @method('PUT')';
        document.body.appendChild(form);
        form.submit();
    }

    function initAdminUserTable() {
        const table = new Tabulator('#adminUserTable', {
            data: @json($adminUsers),
            layout: 'fitColumns',
            pagination: true,
            paginationSize: 10,
            placeholder: @json(__('admin/common.no_data')),
            columns: [
                {
                    // เลขลำดับรันต่อเนื่องข้ามหน้า (หน้า 1 = 1-10, หน้า 2 = 11-20, ...)
                    // ต่างจาก formatter: 'rownum' ที่นับใหม่ 1 ทุกหน้า
                    title: @json(__('admin/common.no')),
                    width: 70,
                    hozAlign: 'center',
                    headerSort: false,
                    formatter: (cell) => (table.getPage() - 1) * table.getPageSize() + cell.getRow().getPosition(true),
                },
                { title: @json(__('admin/common.fullname')), field: 'fullname', formatter: (cell) => cell.getValue() ?? '-' },
                { title: 'Email', field: 'email' },
                { title: @json(__('admin/permission.permission_group_name')), field: 'group_name', formatter: (cell) => cell.getValue() ?? @json(__('admin/permission.no_group')) },
                {
                    title: @json(__('admin/permission.cancel_permission')),
                    hozAlign: 'center',
                    headerSort: false,
                    formatter: () => '<button type="button" class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i></button>',
                    cellClick: (e, cell) => {
                        const row = cell.getRow().getData();
                        cancelPermission(row.id, row.fullname ?? row.email);
                    },
                },
            ],
        });

        // ช่อง search เดียว ค้นทุกคอลัมน์พร้อมกัน (เหมือน DataTables global search)
        document.getElementById('adminUserSearch').addEventListener('input', function (e) {
            const keyword = e.target.value.toLowerCase();

            table.setFilter((row) => {
                return Object.values(row).some((value) =>
                    String(value ?? '').toLowerCase().includes(keyword)
                );
            });
        });
    }

    // admin.js (module) โหลดแบบ deferred — ไม่การันตีว่าจะเสร็จก่อน
    // script ตรงนี้รัน จึงเช็คว่า Tabulator พร้อมหรือยัง ถ้ายังให้รอ
    // event 'admin-scripts-ready' ที่ admin.js ยิงออกมาตอนโหลดเสร็จ
    if (window.Tabulator) {
        initAdminUserTable();
    } else {
        window.addEventListener('admin-scripts-ready', initAdminUserTable);
    }
</script>
@endpush
