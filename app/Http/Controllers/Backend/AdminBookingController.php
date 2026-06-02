<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRoomList;
use App\Models\RoomBookedDate;
use App\Models\RoomNumber;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    // Booking List Method
    public function BookingList()
    {
        $allData = Booking::orderBy('id', 'desc')->get();
        return view('backend.booking.booking_list', compact('allData'));
    }

    // Edit Booking Method
    public function EditBooking($id)
    {
        $editData = Booking::with('room')->find($id);
        return view('backend.booking.edit_booking', compact('editData'));
    }

    // Update Booking Status Method
    
    public function UpdateBookingStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $booking->payment_status = $request->payment_status;
        $booking->save();

        $map = [
            'Confirmed' => 'confirm',
            'Completed' => 'complete',
            'Cancelled' => 'cancel',
        ];

        try {
            if (isset($map[$request->status])) {
                $booking->transition($map[$request->status]);
            }

            $notification = [
                'message' => 'Updated Information Successfully.',
                'alert-type' => 'success'
            ];

        } catch (\Exception $e) {
            $notification = [
                'message' => $e->getMessage(),
                'alert-type' => 'error'
            ];
        }

        return redirect()->back()->with($notification);
    }


    // Update Booking Method
    public function UpdateBooking(Request $request, $id)
    {
        // Kiểm tra số lượng phòng vượt quá số lượng phòng có sẵn
        if ($request->available_room < $request->number_of_rooms) {
            $notification = array(
                'message' => 'Something want to wrong!',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }

        // Cập nhật thông tin đặt phòng
        $data = Booking::find($id);
        $data->number_of_rooms = $request->number_of_rooms;
        $data->check_in = date('d-m-Y', strtotime($request->check_in));
        $data->check_out = date('d-m-Y', strtotime($request->check_out));
        $data->save();

        // Cập nhật ngày đặt phòng mới
        // Xóa các ngày cũ đã đặt 
        RoomBookedDate::where('booking_id', $id)->delete();
        // Xóa các phòng đã gán khi thay đổi ngày đặt phòng
        BookingRoomList::where('booking_id', $id)->delete();

        // Room Booked Dates
        $startDate = date('d-m-Y', strtotime($request->check_in));
        $endDate = date('d-m-Y', strtotime($request->check_out));

        // Không lấy ngày checkout - trừ 1 ngày
        $endDate = Carbon::create($endDate)->subDay();

        // Tạo danh sách các ngày booking
        $day_period = CarbonPeriod::create($startDate, $endDate);

        foreach ($day_period as $period) {
            $booked_dates = new RoomBookedDate();
            $booked_dates->booking_id = $id;
            $booked_dates->room_id = $data->rooms_id;
            $booked_dates->book_date = $period->format('Y-m-d');
            $booked_dates->save();
        }

        $notification = array(
            'message' => 'Updated booking successfully!',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    // Assign Room Method
    public function AssignRoom($booking_id)
    {
        $booking = Booking::find($booking_id);

        // Lấy danh sách tất cả ngày đã đặt của một booking
        $booking_date_array = RoomBookedDate::where('booking_id', $booking_id)->pluck('book_date')->toArray();

        // Kiểm tra các ngày booking bị trùng nhau => Lấy ra danh sách các booking_id
        $check_date_booking_ids = RoomBookedDate::whereIn('book_date', $booking_date_array)
            ->where('room_id', $booking->rooms_id)->distinct()->pluck('booking_id')->toArray();

        // Lấy ra danh sách bookings bị trùng nhau
        $bookings_ids = Booking::whereIn('id', $check_date_booking_ids)->pluck('id')->toArray();

        // Lấy ra danh sách các số phòng đã đặt -> tương ứng các phòng
        $assign_room_ids = BookingRoomList::whereIn('booking_id', $bookings_ids)->pluck('room_number_id')->toArray();

        // Lấy ra danh sách các số phòng chưa được đặt (Chưa được gán)
        $room_numbers = RoomNumber::where('rooms_id', $booking->rooms_id)->whereNotIn('id', $assign_room_ids)
            ->where('status', 'Active')->get();

        return view('backend.booking.assign_room', compact('booking', 'room_numbers'));
    }

    // Assign Room Store Method
    public function AssignRoomStore($booking_id, $room_number_id)
    {
        $booking = Booking::find($booking_id);

        // Đếm số lượng phòng đã gán của đơn đặt phòng đó
        $check_data = BookingRoomList::where('booking_id', $booking_id)->count();

        // Số lượng phòng đã gán không được vượt quá số lượng phòng đã đặt
        if ($check_data < $booking->number_of_rooms) {
            $assign_data = new BookingRoomList();
            $assign_data->booking_id = $booking_id;
            $assign_data->room_id = $booking->rooms_id;
            $assign_data->room_number_id = $room_number_id;
            $assign_data->save();

            $notification = array(
                'message' => 'Assign room successfully!',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Assign room already',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }
    }

    // Assign Room Delete Method
    public function AssignRoomDelete($id)
    {
        $assign_room = BookingRoomList::find($id);
        $assign_room->delete();

        $notification = array(
            'message' => 'Deleted assign room successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
