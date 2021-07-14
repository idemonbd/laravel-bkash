<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.ico') }}">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel - bKash Payment Integration</title>
</head>

<body>

    <div class="container">
        {{ request()->session()->get('token') }}
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-5">
                    <div class="card-header">
                        {{ $order->product_name }}
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $order->product_name }}</h5>
                        <p class="card-text amount">{{ $order->amount }}</p>
                        <p class="card-text invoice">{{ $order->invoice }}</p>
                        @if ($order->status == 'Pending')
                            <button class="btn btn-primary" id="bKash_button">Pay with bKash</button>
                        @else
                            <h4><span class="badge badge-success">Paid</span></h4>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-1.8.3.min.js"
        integrity="sha256-YcbK69I5IXQftf/mYD8WY0/KmEDCv1asggHpJk1trM8=" crossorigin="anonymous"></script>

    <script id="myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js">
    </script>

    @include('scripts')
</body>

</html>
