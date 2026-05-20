@extends('frontend.home_page')

@section('home')

<div class="container pt-100 pb-70">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card">

                <div class="card-header">
                    <h3>Stripe Payment</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('stripe.order') }}" method="POST">

                        @csrf

                        <div class="form-group mb-3">
                            <label>Name On Card</label>
                            <input type="text"
                                   name="name_on_card"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Card Number</label>
                            <input type="text"
                                   name="card_number"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group mb-3">
                            <label>CVC</label>
                            <input type="text"
                                   name="card_cvc"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Expire Month</label>
                            <input type="text"
                                   name="card_expiry_month"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Expire Year</label>
                            <input type="text"
                                   name="card_expiry_year"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-primary w-100">

                            Pay Now

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection