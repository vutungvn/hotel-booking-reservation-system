@extends("admin.admin_dashboard")

@section("admin")
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <div class="page-content">
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5">

            <div class="col">
                <div class="card radius-10 border-start  border-3 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Booking Number</p>
                                <h4 class="my-1 text-info">{{ $editData->code }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-bookmark-alt'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start  border-3 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Booking Date</p>
                                <h4 class="my-1 text-danger">
                                    {{ $editData->created_at->format('d-m-Y') }}
                                </h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                <i class='bx bx-calendar'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-3 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Payment Method</p>
                                <h4 class="my-1 text-success">{{ $editData->payment_method }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                <i class='bx bx-credit-card'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start  border-3 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Payment Status</p>
                                <h4 class="my-1 text-warning">@if ($editData->payment_status == '1') <span
                                class="text-success">Complete</span> @else
                                        <span class="text-danger">Pending</span> @endif
                                </h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i
                                    class='bx bx-wallet-alt'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start  border-3 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Booking Status</p>

                                <h4 class="my-1">
                                    @if ($editData->isPending())
                                        <span class="text-warning">Pending</span>

                                    @elseif ($editData->isConfirmed())
                                        <span class="text-primary">Confirmed</span>

                                    @elseif ($editData->isCompleted())
                                        <span class="text-success">Completed</span>

                                    @elseif ($editData->isCancelled())
                                        <span class="text-danger">Cancelled</span>

                                    @endif
                                </h4>

                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i
                                    class='bx bx-check-shield'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!--end row-->

        <div class="row">
            <div class="col-12 col-lg-8 d-flex">
                <div class="card radius-10 w-100">
                    <style>
                        .booking-card {
                            border: none;
                            border-radius: 16px;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                            overflow: hidden;
                        }

                        .booking-card .card-header {
                            background: linear-gradient(45deg, #0d6efd, #3a8bfd);
                            color: white;
                            padding: 18px 24px;
                            font-size: 20px;
                            font-weight: 600;
                        }

                        .custom-table thead {
                            background-color: #f8f9fa;
                        }

                        .custom-table th {
                            font-weight: 600;
                            color: #444;
                            padding: 14px;
                            white-space: nowrap;
                        }

                        .custom-table td {
                            vertical-align: middle;
                            padding: 14px;
                        }

                        .price-text {
                            font-weight: 700;
                            color: #198754;
                        }

                        .summary-table td {
                            padding: 10px 15px;
                            border: none;
                        }

                        .summary-table tr td:first-child {
                            font-weight: 600;
                            color: #555;
                        }

                        .summary-table tr:last-child td {
                            font-size: 18px;
                            border-top: 2px solid #dee2e6;
                            padding-top: 15px;
                        }

                        .custom-select {
                            height: 48px;
                            border-radius: 10px;
                            border: 1px solid #ced4da;
                        }

                        .btn-update {
                            padding: 12px 30px;
                            border-radius: 12px;
                            font-weight: 600;
                            font-size: 16px;
                            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
                        }

                        .badge-date {
                            padding: 8px 14px;
                            font-size: 13px;
                            border-radius: 8px;
                        }
                    </style>

                    <div class="card booking-card">

                        <div class="card-header">
                            Booking Information
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-hover align-middle custom-table">
                                    <thead>
                                        <tr>
                                            <th>Room Type</th>
                                            <th>Total Rooms</th>
                                            <th>Price</th>
                                            <th>Check In / Out</th>
                                            <th>Total Days</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>{{ optional(optional($editData->room)->type)->name ?? 'N/A' }}</strong>
                                            </td>

                                            <td>
                                                {{ $editData->number_of_rooms }}
                                            </td>

                                            <td class="price-text">
                                                ${{ $editData->actual_price }}
                                            </td>

                                            <td>
                                                <span class="badge bg-primary badge-date">
                                                    {{ $editData->check_in }}
                                                </span>

                                                <br><br>

                                                <span class="badge bg-warning text-dark badge-date">
                                                    {{ $editData->check_out }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $editData->total_night }} Nights
                                            </td>

                                            <td class="price-text">
                                                ${{ $editData->actual_price * $editData->number_of_rooms }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="row mt-4">
                                <div class="col-md-5 ms-auto">
                                    <div class="card border-0 shadow-sm rounded-4">
                                        <div class="card-body">
                                            <table class="table summary-table mb-0">
                                                <tr>
                                                    <td>Subtotal</td>
                                                    <td>${{ $editData->subtotal }}</td>
                                                </tr>

                                                <tr>
                                                    <td>Discount</td>
                                                    <td class="text-danger">
                                                        -${{ $editData->discount }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td><strong>Grand Total</strong></td>
                                                    <td class="text-success">
                                                        <strong>${{ $editData->total_price }}</strong>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div style="clear: both"></div>
                                <div style="margin-top: 40px;">
                                    <a href="javascript::void(0)" class="btn btn-primary assign_room"
                                        style="margin-bottom: 20px">Assign Room</a>

                                    @php
                                        // Lấy tất cả phòng đã gán cho booking hiện tại
                                        $assign_rooms = App\Models\BookingRoomList::with('room_numbers')->where('booking_id', $editData->id)->get();
                                    @endphp

                                    @if (count($assign_rooms) > 0)
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Room Number</th>
                                                <th>Action</th>
                                            </tr>
                                            @foreach ($assign_rooms as $assign_room)
                                                <tr>
                                                    <td>{{ $assign_room->room_numbers->room_number }}</td>
                                                    <td>
                                                        <a href="{{ route('assign_room_delete', $assign_room->id) }}"
                                                            class="btn btn-danger px-2 radius-30" id="delete">Delete</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @else
                                        <div class="alert alert-danger text-center">
                                            Not Found Assign Room
                                        </div>
                                    @endif
                                </div>
                            </div>
                            {{-- End Table Responsive --}}

                            <!-- Form -->
                            <form action="{{ route('update.booking.status', $editData->id) }}" method="POST">
                                @csrf

                                <div class="row mt-5">

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
                                            Payment Status
                                        </label>

                                        <select name="payment_status" class="form-select custom-select">

                                            <option value="0" {{ $editData->payment_status == '0' ? 'selected' : '' }}>
                                                Pending
                                            </option>

                                            <option value="1" {{ $editData->payment_status == '1' ? 'selected' : '' }}>
                                                Complete
                                            </option>

                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
                                            Booking Status
                                        </label>

                                        <select class="form-select custom-select" disabled>
                                            <option>{{ $editData->status }}</option>
                                        </select>

                                        <div class="mt-2 text-center">                        
                                            <!-- ACTION -->
                                            @if ($editData->isPending())
                                                <button name="status" value="Confirmed" class="btn btn-primary">Confirm</button>
                                                <button name="status" value="Cancelled" class="btn btn-danger">Cancel</button>
                                            @endif

                                            @if ($editData->isConfirmed())
                                                <button name="status" value="Completed" class="btn btn-primary">Complete</button>
                                                <button name="status" value="Cancelled" class="btn btn-danger">Cancel</button>
                                            @endif
                                        </div>      
                                    </div>

                                    <div class="col-md-12 text-start">
                                        <button type="submit" class="btn btn-primary btn-update">
                                            <i class='bx bx-save'></i>
                                            Update Booking
                                        </button>
                                    </div>

                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                {{-- End Booking Information --}}
            </div>

            <div class="col-12 col-lg-4">
                <div class="card radius-10 w-100">
                    <style>
                        .manage-booking-card {
                            border: none;
                            border-radius: 18px;
                            overflow: hidden;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                        }

                        .manage-booking-header {
                            background: linear-gradient(45deg, #0d6efd, #3b82f6);
                            padding: 18px 22px;
                            color: white;
                        }

                        .manage-booking-header h6 {
                            margin: 0;
                            font-size: 18px;
                            font-weight: 600;
                        }

                        . {
                            font-weight: 600;
                            margin-bottom: 8px;
                            color: #444;
                        }

                        .custom-input {
                            height: 50px;
                            border-radius: 12px;
                            border: 1px solid #dcdcdc;
                            transition: 0.3s;
                        }

                        .custom-input:focus {
                            border-color: #0d6efd;
                            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, .15);
                        }

                        .availability-box {
                            background: #f8f9fa;
                            border-radius: 12px;
                            padding: 14px 16px;
                            font-weight: 500;
                        }

                        .availability-badge {
                            padding: 6px 12px;
                            border-radius: 8px;
                            font-size: 13px;
                        }

                        .btn-update-booking {
                            height: 50px;
                            border-radius: 12px;
                            font-weight: 600;
                            font-size: 15px;
                            padding: 0 28px;
                            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
                        }
                    </style>

                    <div class="card manage-booking-card">

                        <!-- Header -->
                        <div class="manage-booking-header">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-calendar-edit me-2 fs-4'></i>

                                <h6 class="text-white">
                                    Manage Room And Date
                                </h6>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body p-4">

                            <form action="{{ route('update.booking', $editData->id) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Check In -->
                                    <div class="col-md-12 mb-4">
                                        <label class="">
                                            Check-In Date
                                        </label>
                                        <input type="date" class="form-control custom-input" name="check_in" id="check_in"
                                            value="{{ \Carbon\Carbon::createFromFormat('d-m-Y', $editData->check_in)->format('Y-m-d') }}"
                                            required>
                                    </div>

                                    <!-- Check Out -->
                                    <div class="col-md-12 mb-4">
                                        <label class="">
                                            Check-Out Date
                                        </label>
                                        <input type="date" class="form-control custom-input" name="check_out" id="check_out"
                                            value="{{ \Carbon\Carbon::createFromFormat('d-m-Y', $editData->check_out)->format('Y-m-d') }}"
                                            required>
                                    </div>

                                    <!-- Room -->
                                    <div class="col-md-12 mb-4">
                                        <label class="">
                                            Number Of Rooms
                                        </label>
                                        <input type="number" class="form-control custom-input" name="number_of_rooms"
                                            value="{{ $editData->number_of_rooms }}" min="1" required>
                                    </div>

                                    <input type="hidden" name="available_room" id="available_room" class="form-control">

                                    <!-- Availability -->
                                    <div class="col-md-12 mb-4">
                                        <div class="availability-box">
                                            <div class="d-flex align-items-center">
                                                <i class='bx bx-check-circle text-success me-2 fs-5'></i>
                                                <span> Availability: <span class="text-success availability"></span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-update-booking w-100">
                                            <i class='bx bx-save me-1'></i>
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                {{-- End Manage Room And Date --}}

                <div class="card radius-10 w-100">
                    <div class="card manage-booking-card">
                        <!-- Header -->
                        <div class="manage-booking-header">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-calendar-edit me-2 fs-4'></i>

                                <h6 class="text-white">
                                    Customer Information
                                </h6>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                    Name <span class="badge bg-success rounded-pill">{{ $editData->user->name }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    Email <span class="badge bg-danger rounded-pill">{{ $editData->user->email }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    Phone <span class="badge bg-primary rounded-pill">{{ $editData->user->phone }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    Country <span
                                        class="badge bg-warning text-dark rounded-pill">{{ $editData->country }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    State <span class="badge bg-success rounded-pill">{{ $editData->state }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    Zip Code <span class="badge bg-danger rounded-pill">{{ $editData->zip_code }}</span>
                                </li>
                                <li
                                    class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                    Address <span class="badge bg-primary rounded-pill">{{ $editData->address }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- End Customer Information --}}
            </div><!--end row-->
        </div>

        <!-- Modal -->
        <div class="modal fade myModal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Rooms</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                </div>
            </div>
        </div>

        {{-- Check Availability --}}
        <script>
            $(document).ready(function () {
                getAvailability();

                $(".assign_room").on("click", function () {
                    $.ajax({
                        url: "{{ route('assign_room', $editData->id) }}",
                        success: function (data) {
                            $('.myModal .modal-body').html(data);
                            $('.myModal').modal('show');
                        }
                    })

                    return false;
                })
            });

            function getAvailability() {
                var check_in = $('#check_in').val();
                var check_out = $('#check_out').val();
                var room_id = "{{ $editData->rooms_id }}";

                $.ajax({
                    url: "{{ route('check_room_availability') }}",
                    data: {
                        room_id: room_id,
                        check_in: check_in,
                        check_out: check_out
                    },
                    success: function (data) {
                        $('.availability').text(data['available_room']);
                        $('#available_room').val(data['available_room']);
                    }
                })
            }
        </script>
@endsection