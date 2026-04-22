@extends("admin.admin_dashboard")

@section("admin")
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">All Teams</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-group"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">All Teams</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('add.team') }}" class="btn btn-primary px-3"><i class="bx bx-plus me-1"></i>Add Team</a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">All Teams</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Facebook</th>
                                <th>Tiktok</th>
                                <th>Instagram</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($team as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($item->image)
                                            <img src="{{ asset($item->image) }}" alt="{{ $item->name }}"
                                                style="width: 70px; height: 40px;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->position }}</td>
                                    <td>{{ Str::limit($item->facebook, 20, '...') }}</td>
                                    <td>{{ Str::limit($item->tiktok, 20, '...') }}</td>
                                    <td>{{ Str::limit($item->instagram, 20, '...') }}</td>
                                    <td>
                                        <a href="{{ route('edit.team', $item->id) }}"
                                            class="btn btn-warning px-3 radius-30">Edit</a>
                                        {{-- form Xóa --}}
                                        <a href="{{ route('delete.team', $item->id) }}" class="btn btn-danger px-3 radius-30"
                                            id="delete">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection