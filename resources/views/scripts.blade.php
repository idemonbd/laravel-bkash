<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        console.log('Get and put access token into session');
        $.ajax({
            url: "{!! route('token') !!}",
            type: 'POST',
            contentType: 'application/json',
            success: function(res) {
                console.log('Access token set successfully');
                bkash_init();
            },
            error: function() {
                console.log('Error on setting access token');
            }
        });
    });

    function bkash_init() {
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

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: paymentRequest,
            createRequest: function(request) {
                console.log('Send Create payment Request to server with::');
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
                console.log('Payment exicuting');
                $.ajax({
                    url: paymentConfig.executeCheckoutURL + "?paymentID=" + paymentID,
                    type: 'GET',
                    contentType: 'application/json',
                    success: function(data) {
                        console.log('Payment execute done...');
                        console.log(JSON.stringify(data));

                        data = JSON.parse(data);
                        if (data && data.paymentID != null) {
                            console.log('Payment Successfull : ' + JSON.stringify(data));
                            // window.location.href = "{!! route('orders.index') !!}";
                        } else {
                            console.log('execute invalid data response');
                            bKash.execute().onError();
                        }
                    },
                    error: function() {
                        console.log('error when executing payment');
                        bKash.execute().onError();
                    }
                });
            },
            onClose:function(){
                console.log('Cancelled');
            }
        });
    }

    function callReconfigure(val) {
        console.log('call reconfigure function');
        bKash.reconfigure(val);
    }

    function clickPayButton() {
        console.log('triggering bkash payment button');
        $("#bKash_button").trigger('click');
    }
</script>
