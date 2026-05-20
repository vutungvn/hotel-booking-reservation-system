<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBookedDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Omnipay;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Stripe;
use Stripe\Charge;

class BookingController extends Controller
{
    // Checkout Method
    public function Checkout()
    {
        // Kiểm tra session có data không
        if (Session::has('book_date')) {
            // Nếu có data thì lấy data từ session
            $book_data = Session::get('book_date');
            $room = Room::find($book_data['room_id']);

            // Tính số nights giữa check_in và check_out
            $toDate = Carbon::parse($book_data['check_in']);
            $fromDate = Carbon::parse($book_data['check_out']);
            $nights = $toDate->diffInDays($fromDate);

            return view('frontend.checkout.checkout', compact('book_data', 'room', 'nights'));
        } else {
            // Nếu không có data thì redirect về trang chủ
            $notification = [
                'message' => 'Something went to wrong!',
                'alert-type' => 'error',
            ];

            return redirect('/')->with($notification);
        }
    }

    // Booking Store Method
    public function BookingStore(Request $request, $id)
    {
        // Valtedate data
        $validateData = $request->validate([
            'check_in' => 'required',
            'check_out' => 'required',
            'person' => 'required',
            'number_of_rooms' => 'required',
        ]);

        // Kiểm tra chọn số lượng phòng vượt quá số lượng phòng còn trống
        if ($request->available_room < $request->number_of_rooms) {
            $notification = [
                'message' => 'Something went to wrong!',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);
        }

        // Lưu dữ liệu vào session
        Session::forget('book_date');
        $data = [];
        $data['room_id'] = $id;
        $data['check_in'] = date('d-m-Y', strtotime($request->check_in));
        $data['check_out'] = date('d-m-Y', strtotime($request->check_out));
        $data['person'] = $request->person;
        $data['number_of_rooms'] = $request->number_of_rooms;
        $data['available_room'] = $request->available_room;
        Session::put('book_date', $data);

        return redirect()->route('checkout');
    }

    // Place Order Method
    public function PlaceOrder()
    {
        return view('frontend.checkout.place_order');
    }

    // Checkout Store Method
    public function CheckoutStore(Request $request)
    {
        // Check session tồn tại
        if (! Session::has('book_date')) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Session expired');
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);

        // Lấy dữ liệu từ session
        $book_data = Session::get('book_date');

        // Lấy room
        $room = Room::find($book_data['room_id']);

        // Tính số đêm
        $toDate = Carbon::parse($book_data['check_in']);
        $fromDate = Carbon::parse($book_data['check_out']);

        $total_nights = $toDate->diffInDays($fromDate);

        // Tính tiền
        $subtotal = $room->price * $total_nights * $book_data['number_of_rooms'];

        // Discount
        $discount = ($room->discount / 100) * $subtotal;

        // Total Price
        $total_price = $subtotal - $discount;

        // Generate booking code 9 số
        $code = rand(100000000, 999999999);

        if ($request->payment_method == 'paypal') {

            Session::put('checkout_data', [

                'name' => $request->name,
                'email' => $request->email,
                'country' => $request->country,
                'phone' => $request->phone,
                'address' => $request->address,
                'state' => $request->state,
                'zip_code' => $request->zip_code,

                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_price' => $total_price,
                'total_nights' => $total_nights,

            ]);

            return redirect()
                ->route('paypal.payment');
        }

        // Insert Data Booking
        $booking = new Booking;

        $booking->rooms_id = $room->id;
        $booking->user_id = Auth::id();

        $booking->check_in = date('d-m-Y', strtotime($book_data['check_in']));
        $booking->check_out = date('d-m-Y', strtotime($book_data['check_out']));

        $booking->person = $book_data['person'];
        $booking->number_of_rooms = $book_data['number_of_rooms'];

        $booking->total_night = $total_nights;

        $booking->actual_price = $room->price;

        $booking->subtotal = $subtotal;
        $booking->discount = $discount;
        $booking->total_price = $total_price;

        $booking->payment_method = $request->payment_method;
        $booking->transaction_id = '';
        $booking->payment_status = 0;

        $booking->name = $request->name;
        $booking->email = $request->email;
        $booking->phone = $request->phone;
        $booking->country = $request->country;
        $booking->state = $request->state;
        $booking->zip_code = $request->zip_code;
        $booking->address = $request->address;

        $booking->code = $code;
        $booking->status = 0;

        $booking->created_at = Carbon::now();

        $booking->save();

        // Room Booked Dates
        $startDate = Carbon::createFromFormat('d-m-Y', $book_data['check_in']);
        $endDate = Carbon::createFromFormat('d-m-Y', $book_data['check_out']);

        // Không lấy ngày checkout - trừ 1 ngày
        $endDate = $endDate->subDay();

        // Tạo danh sách các ngày booking
        $day_period = CarbonPeriod::create($startDate, $endDate);

        foreach ($day_period as $period) {
            $booked_dates = new RoomBookedDate;
            $booked_dates->booking_id = $booking->id;
            $booked_dates->room_id = $room->id;
            $booked_dates->book_date = $period->format('Y-m-d');
            $booked_dates->save();
        }

        // Clear Session
        Session::forget('book_date');

        // Notification
        $notification = [
            'message' => 'Add Booking Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('place.order')->with($notification);
    }

    public function vnpayPayment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);

        $book_data = Session::get('book_date');
        if (empty($book_data)) {
            $notification = [
                'message' => 'Missing booking session data.',
                'alert-type' => 'error',
            ];

            return redirect()->route('checkout')->with($notification);
        }

        $room = Room::find($book_data['room_id']);
        if (! $room) {
            $notification = [
                'message' => 'Room not found.',
                'alert-type' => 'error',
            ];

            return redirect()->route('checkout')->with($notification);
        }

        $toDate = Carbon::parse($book_data['check_in']);
        $fromDate = Carbon::parse($book_data['check_out']);
        $total_nights = $toDate->diffInDays($fromDate);

        $subtotal = $room->price * $total_nights * $book_data['number_of_rooms'];
        $discount = ($room->discount / 100) * $subtotal;
        $total_price = $subtotal - $discount;

        $code = rand(100000000, 999999999);

        $booking = new Booking;
        $booking->rooms_id = $room->id;
        $booking->user_id = Auth::id();
        $booking->check_in = $book_data['check_in'];
        $booking->check_out = $book_data['check_out'];
        $booking->person = $book_data['person'];
        $booking->number_of_rooms = $book_data['number_of_rooms'];
        $booking->total_night = $total_nights;
        $booking->actual_price = $room->price;
        $booking->subtotal = $subtotal;
        $booking->discount = $discount;
        $booking->total_price = $total_price;
        $booking->payment_method = 'VN Pay';
        $booking->transaction_id = '';
        $booking->payment_status = 0;
        $booking->code = $code;
        $booking->status = 0;
        $booking->name = $validated['name'];
        $booking->email = $validated['email'];
        $booking->phone = $validated['phone'];
        $booking->country = $validated['country'];
        $booking->state = $validated['state'];
        $booking->zip_code = $validated['zip_code'];
        $booking->address = $validated['address'];
        $booking->created_at = Carbon::now();
        $booking->save();

        $gateway = Omnipay::create('VnPay');
        $gateway->initialize([
            'tmnCode' => config('omnipay.gateways.VNPay.tmn_code'),
            'hashSecret' => config('omnipay.gateways.VNPay.hash_secret'),
            'returnUrl' => route('vnpay.return'),
            'testMode' => (bool) config('omnipay.gateways.VNPay.test_mode'),
        ]);

        $response = $gateway->purchase([
            'txnRef' => (string) $code,
            'orderInfo' => 'Thanh toan don hang '.$code,
            'amount' => (int) round($total_price),
            'currency' => config('omnipay.gateways.VNPay.currency', 'VND'),
            'returnUrl' => route('vnpay.return'),
            'locale' => config('omnipay.gateways.VNPay.locale', 'vi'),
        ])->send();

        if ($response->isRedirect() && $response instanceof RedirectResponseInterface) {
            return redirect()->away($response->getRedirectUrl());
        }

        return back()->with('error', 'Unable to create VNPay payment request.');
    }

    public function vnpayReturn(Request $request)
    {
        $data = $request->query->all();
        if (empty($data)) {
            return redirect()->route('place.order')->with('error', 'Missing VNPay response data.');
        }

        $bookingCode = $data['vnp_TxnRef'] ?? null;
        if (! $bookingCode) {
            return redirect()->route('place.order')->with('error', 'Missing VNPay transaction reference.');
        }

        $booking = Booking::where('code', $bookingCode)->first();
        if (! $booking) {
            return redirect()->route('place.order')->with('error', 'Booking not found for VNPay response.');
        }

        $hashSecret = config('omnipay.gateways.VNPay.hash_secret');
        $secureHash = $data['vnp_SecureHash'] ?? '';
        if (! $hashSecret || ! $secureHash) {
            return redirect()->route('place.order')->with('error', 'Missing VNPay signature data.');
        }

        $hashData = $data;
        unset($hashData['vnp_SecureHash'], $hashData['vnp_SecureHashType']);
        ksort($hashData);

        $hashString = '';
        $hashStringEncoded = '';
        foreach ($hashData as $key => $value) {
            if (strpos($key, 'vnp_') === 0 && $value !== '' && $value !== null) {
                $hashString .= $key.'='.$value.'&';
                $hashStringEncoded .= urlencode($key).'='.urlencode((string) $value).'&';
            }
        }

        $hashString = rtrim($hashString, '&');
        $hashStringEncoded = rtrim($hashStringEncoded, '&');

        $calculatedHash = hash_hmac('sha512', $hashString, $hashSecret);
        $calculatedHashEncoded = hash_hmac('sha512', $hashStringEncoded, $hashSecret);

        $isValidSignature =
            hash_equals(strtolower($secureHash), strtolower($calculatedHash)) ||
            hash_equals(strtolower($secureHash), strtolower($calculatedHashEncoded));

        if (! $isValidSignature) {
            return redirect()->route('place.order')->with('error', 'Invalid VNPay signature.');
        }

        $responseCode = $data['vnp_ResponseCode'] ?? null;
        $transactionStatus = $data['vnp_TransactionStatus'] ?? null;
        $paidAmount = isset($data['vnp_Amount']) ? (int) $data['vnp_Amount'] : null;
        $expectedAmount = (int) round(((float) $booking->total_price) * 100);

        if ($responseCode !== '00') {
            return redirect()->route('place.order')->with('error', 'VNPay payment failed.');
        }

        if ($transactionStatus !== null && $transactionStatus !== '00') {
            return redirect()->route('place.order')->with('error', 'VNPay payment failed.');
        }

        if ($paidAmount !== null && $paidAmount !== $expectedAmount) {
            return redirect()->route('place.order')->with('error', 'VNPay amount mismatch.');
        }

        if ((int) $booking->payment_status === 1) {
            return redirect()->route('place.order')->with('success', 'Payment already processed');
        }

        $booking->payment_status = 1;
        $booking->transaction_id = $data['vnp_TransactionNo'] ?? ($data['vnp_BankTranNo'] ?? '');
        $booking->payment_method = $booking->payment_method ?: 'VN Pay';
        $booking->save();

        $alreadyBooked = RoomBookedDate::where('booking_id', $booking->id)->exists();
        if (! $alreadyBooked) {
            $startDate = Carbon::createFromFormat('d-m-Y', $booking->check_in);
            $endDate = Carbon::createFromFormat('d-m-Y', $booking->check_out);
            $endDate = $endDate->subDay();

            $day_period = CarbonPeriod::create($startDate, $endDate);
            foreach ($day_period as $period) {
                $booked_dates = new RoomBookedDate;
                $booked_dates->booking_id = $booking->id;
                $booked_dates->room_id = $booking->rooms_id;
                $booked_dates->book_date = $period->format('Y-m-d');
                $booked_dates->save();
            }
        }

        Session::forget('book_date');

        $notification = [
            'message' => 'Add Booking Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('place.order')->with($notification);
    }

    // Paypal Payment
    public function PaypalPayment()
    {
        $checkout = Session::get('checkout_data');

        if (! $checkout) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Session expired');
        }

        if (
            ! isset($checkout['total_price']) ||
            $checkout['total_price'] <= 0
        ) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Invalid payment amount');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $response = $provider->createOrder([
            'intent' => 'CAPTURE',

            'application_context' => [
                'return_url' => route('paypal.success'),
                'cancel_url' => route('paypal.cancel'),
            ],

            'purchase_units' => [
                [
                    'amount' => [

                        'currency_code' => 'USD',

                        'value' => number_format(
                            $checkout['total_price'],
                            2,
                            '.',
                            ''
                        ),
                    ],
                ],
            ],
        ]);

        if (! empty($response['id']) && isset($response['links'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return redirect()
                        ->away($link['href']);
                }
            }
        }

        return redirect()->route('checkout')
            ->with('error', 'Paypal payment failed');
    }

    // Paypal Success

    public function PaypalSuccess(Request $request)
    {
        if (! Session::has('checkout_data') || ! Session::has('book_date')) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Session expired');
        }

        if (! $request->token || empty($request->token)) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Invalid PayPal token');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $response = $provider->capturePaymentOrder($request->token);

        $existingBooking = Booking::where(
            'transaction_id',
            $response['id']
        )->first();

        if ($existingBooking) {
            return redirect()
                ->route('place.order')
                ->with('success', 'Payment already processed');
        }

        if (isset($response['status']) && $response['status'] == 'COMPLETED' && isset($response['id'])) {

            $book_data = Session::get('book_date');
            $checkout = Session::get('checkout_data');
            $room = Room::find($book_data['room_id']);
            if (! $room) {
                Session::forget('book_date');
                Session::forget('checkout_data');

                return redirect('/')
                    ->with('error', 'Room not found');
            }
            $booking = new Booking;

            $booking->rooms_id = $room->id;
            $booking->user_id = Auth::id();
            $booking->check_in = $book_data['check_in'];
            $booking->check_out = $book_data['check_out'];
            $booking->person = $book_data['person'];
            $booking->number_of_rooms = $book_data['number_of_rooms'];
            $booking->total_night = $checkout['total_nights'];
            $booking->actual_price = $room->price;
            $booking->subtotal = $checkout['subtotal'];
            $booking->discount = $checkout['discount'];
            $booking->total_price = $checkout['total_price'];
            $booking->payment_method = 'paypal';

            $booking->transaction_id = $response['id'];

            $booking->payment_status = 1;

            $booking->name = $checkout['name'];
            $booking->email = $checkout['email'];
            $booking->phone = $checkout['phone'];
            $booking->country = $checkout['country'];
            $booking->state = $checkout['state'];
            $booking->zip_code = $checkout['zip_code'];
            $booking->address = $checkout['address'];

            $booking->code = rand(100000000, 999999999);

            $booking->status = 0;

            $booking->created_at = Carbon::now();

            $booking->save();

            // Room booked dates

            $startDate =
                Carbon::createFromFormat(
                    'd-m-Y',
                    $book_data['check_in']
                );

            $endDate =
                Carbon::createFromFormat(
                    'd-m-Y',
                    $book_data['check_out']
                );

            $endDate = $endDate->subDay();

            $day_period =
                CarbonPeriod::create(
                    $startDate,
                    $endDate
                );

            foreach ($day_period as $period) {

                $booked_dates = new RoomBookedDate;

                $booked_dates->booking_id = $booking->id;

                $booked_dates->room_id = $room->id;

                $booked_dates->book_date = $period->format('Y-m-d');

                $booked_dates->save();
            }

            Session::forget('book_date');

            Session::forget('checkout_data');

            // Notification
            $notification = [
                'message' => 'Paypal Payment Successfully',
                'alert-type' => 'success',
            ];

            return redirect()->route('place.order')->with($notification);
        }

        return redirect()
            ->route('checkout')
            ->with('error', 'Payment Failed');
    }

    // Paypal Cancel

    public function PaypalCancel()
    {
        Session::forget('checkout_data');

        return redirect()
            ->route('checkout')
            ->with('error', 'Payment Cancelled');
    }

    public function StripePayment(Request $request)
{
    $request->validate([

        'name' => 'required',
        'email' => 'required|email',
        'country' => 'required',
        'phone' => 'required',
        'address' => 'required',
        'state' => 'required',
        'zip_code' => 'required',

    ]);

    Session::put('stripe_data', [

        'name' => $request->name,
        'email' => $request->email,
        'country' => $request->country,
        'phone' => $request->phone,
        'address' => $request->address,
        'state' => $request->state,
        'zip_code' => $request->zip_code,

    ]);

    return view('frontend.payment.stripe');
}
public function StripeOrder(Request $request)
{
    if (! Session::has('book_date') || ! Session::has('stripe_data')) {

        return redirect()
            ->route('checkout')
            ->with('error', 'Session expired');
    }

    $book_data = Session::get('book_date');

    $stripe_data = Session::get('stripe_data');

    $room = Room::find($book_data['room_id']);

    $toDate = Carbon::parse($book_data['check_in']);

    $fromDate = Carbon::parse($book_data['check_out']);

    $total_nights = $toDate->diffInDays($fromDate);

    $subtotal =
        $room->price *
        $total_nights *
        $book_data['number_of_rooms'];

    $discount =
        ($room->discount / 100) *
        $subtotal;

    $total_price =
        $subtotal - $discount;

    Stripe::setApiKey(env('STRIPE_SECRET'));

    try {

        $charge = Charge::create([

            "amount" => round($total_price * 100),

            "currency" => "usd",

            "source" => "tok_visa",

            "description" => "Room Booking Payment"

        ]);

        $booking = new Booking;

        $booking->rooms_id = $room->id;

        $booking->user_id = Auth::id();

        $booking->check_in = $book_data['check_in'];

        $booking->check_out = $book_data['check_out'];

        $booking->person = $book_data['person'];

        $booking->number_of_rooms = $book_data['number_of_rooms'];

        $booking->total_night = $total_nights;

        $booking->actual_price = $room->price;

        $booking->subtotal = $subtotal;

        $booking->discount = $discount;

        $booking->total_price = $total_price;

        $booking->payment_method = 'Stripe';

        $booking->transaction_id = $charge->id;

        $booking->payment_status = 1;

        $booking->name = $stripe_data['name'];

        $booking->email = $stripe_data['email'];

        $booking->phone = $stripe_data['phone'];

        $booking->country = $stripe_data['country'];

        $booking->state = $stripe_data['state'];

        $booking->zip_code = $stripe_data['zip_code'];

        $booking->address = $stripe_data['address'];

        $booking->code = rand(100000000, 999999999);

        $booking->status = 0;

        $booking->created_at = Carbon::now();

        $booking->save();

        // Room Booked Dates

        $startDate =
            Carbon::createFromFormat(
                'd-m-Y',
                $book_data['check_in']
            );

        $endDate =
            Carbon::createFromFormat(
                'd-m-Y',
                $book_data['check_out']
            );

        $endDate = $endDate->subDay();

        $day_period =
            CarbonPeriod::create(
                $startDate,
                $endDate
            );

        foreach ($day_period as $period) {

            $booked_dates = new RoomBookedDate;

            $booked_dates->booking_id = $booking->id;

            $booked_dates->room_id = $room->id;

            $booked_dates->book_date =
                $period->format('Y-m-d');

            $booked_dates->save();
        }

        Session::forget('book_date');

        Session::forget('stripe_data');

        return redirect()
            ->route('place.order')
            ->with('success', 'Stripe Payment Successfully');

    } catch (\Exception $e) {

        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    }
}
}

// test Paypal tk personal
// email: personal2026@personal.example.com
// password: 12345678

// email: tungdev2k4@personal.example.com
// password: 12345678

// paypal tk business
// business2026@business.example.com
// 12345678

// email: tungdevbussiness@business.example.com
// password: 12345678

// Kiểm tra lịch sử thanh toán paypal
// https://www.sandbox.paypal.com/myaccount/summary
