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

    <script>
        var accessToken = '';

        $(document).ready(function() {
            console.log('Setup Ajax Header');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            console.log('Ajax Header Setup Done');

            console.log('Ajax call to server for get access token');
            $.ajax({
                url: "{!! route('token') !!}",
                type: 'POST',
                contentType: 'application/json',
                success: function(data) {
                    console.log('Got the token form server successfully');
                    console.log(JSON.stringify(data));

                    console.log('Setting access token var');
                    accessToken = JSON.stringify(data);
                },
                error: function() {
                    console.log('Ajax response Error:: dont get token from server');

                }
            });

            // CreatePayment and Execute Payment route 
            var paymentConfig = {
                createCheckoutURL: "{!! route('createpayment') !!}",
                executeCheckoutURL: "{!! route('executepayment') !!}"
            };

            // Execute order post data
            var paymentRequest;
            paymentRequest = {
                amount: $('.amount').text(),
                intent: 'sale',
                invoice: $('.invoice').text()
            };
            console.log('Prepare createPayment post data');
            console.log(JSON.stringify(paymentRequest));

            bKash.init({
                paymentMode: 'checkout',
                paymentRequest: paymentRequest,
                createRequest: function(request) {
                    // when click pay with bkash this will call
                    console.log('Call createPayment Request to server with::');
                    console.log(request);

                    $.ajax({
                        url: paymentConfig.createCheckoutURL + "?amount=" + paymentRequest
                            .amount + "&invoice=" + paymentRequest.invoice,
                        type: 'GET',
                        contentType: 'application/json',
                        success: function(data) {
                            console.log('CreatePayment Success::');
                            console.log(JSON.stringify(data));

                            var obj = JSON.parse(data);

                            if (data && obj.paymentID != null) {
                                paymentID = obj.paymentID;
                                bKash.create().onSuccess(obj);
                            } else {
                                console.log('Error On CreatePayment Request');
                                bKash.create().onError();
                            }
                        },
                        error: function() {
                            console.log('Error On CreatePayment Request');
                            bKash.create().onError();
                        }
                    });
                },

                executeRequestOnAuthorization: function() {
                    console.log('Payment exicute ajax calling');
                    $.ajax({
                        url: paymentConfig.executeCheckoutURL + "?paymentID=" + paymentID,
                        type: 'GET',
                        contentType: 'application/json',
                        success: function(data) {
                            console.log('got data from execute');
                            console.log(JSON.stringify(data));

                            data = JSON.parse(data);
                            if (data && data.paymentID != null) {
                                alert('Payment Successfull : ' + JSON.stringify(data));
                                window.location.href = "{!! route('orders.index') !!}";
                            } else {
                                console.log('error when executing payment');
                                bKash.execute().onError();
                            }
                        },
                        error: function() {
                            console.log('error when executing payment');
                            bKash.execute().onError();
                        }
                    });
                }
            });

            console.log("End of document ready state");
        });

        function callReconfigure(val) {
            console.log('call reconfigure function');
            bKash.reconfigure(val);
        }

        function clickPayButton() {
            console.log('triggering bkash payment button');
            $("#bKash_button").trigger('click');
        }
    </script>
</body>

</html>
