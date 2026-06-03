@extends("admin.admin_dashboard")

@section("admin")
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">All Bookings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-calendar-check"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">All Bookings</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="" class="btn btn-primary px-3"><i class="bx bx-plus me-1"></i>Add
                        Booking</a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">All Bookings</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Booking Number</th>
                                <th>Booking Date</th>
                                <th>Customer</th>
                                <th>Room</th>
                                <th>Check in/out</th>
                                <th>Total Rooms</th>
                                <th>Guests</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allData as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><a href="{{ route('edit_booking', $item->id) }}">{{ $item->code }}</a></td>
                                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $item->user->name }}</td>
                                    <td>{{ optional(optional($item->room)->type)->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-primary">{{ $item->check_in }}</span> <br> <span
                                            class="badge bg-warning text-dark">{{ $item->check_out }}</span></td>
                                    <td>{{ $item->number_of_rooms }}</td>
                                    <td>{{ $item->person }}</td>
                                    <td>@if ($item->payment_status == '1') <span class="badge bg-success">Complete</span> @else
                                    <span class="badge bg-danger">Pending</span> @endif
                                    </td>
                                    <td>
                                        @if ($item->status == 'Pending')
                                            <span class="badge bg-warning">Pending</span>

                                        @elseif ($item->status == 'Confirmed')
                                            <span class="badge bg-primary">Confirmed</span>

                                        @elseif ($item->status == 'Completed')
                                            <span class="badge bg-success">Completed</span>

                                        @elseif ($item->status == 'Cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="" class="btn btn-danger px-3 radius-30" id="delete">Delete</a>
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