$(function () {
    var fullNameField = $('#userFullName');
    var phoneNumberField = $('#userPhone');
    var townshipField = $('#userTownship');
    var townField = $('#userTown');
    var addressField = $('#userAddress');
    var postCodeField = $('#userPostCode');
    var emailField = $('#userEmailAddress');

    $('#checkoutBtn').on('click', function () {
        if (
            fullNameField.val() === "" ||
            phoneNumberField.val() === "" ||
            townshipField.val() === "" ||
            townField.val() === "" ||
            addressField.val() === "" ||
            postCodeField.val() === "" ||
            emailField.val() === "") {
            alert('Попълнете всички полета!');
            return;
        }

        var obj = {
            fullName:fullNameField.val(),
            phoneNumber:phoneNumberField.val(),
            townshipId:townshipField.val(),
            residential:townField.val(),
            address:addressField.val(),
            postCode:postCodeField.val(),
        };


        $.ajax({
            type:"POST",
            url:"/cart/checkout/commit",
            data:{user_address:obj, email:emailField.val()},
            success:function (data) {
                var resp = JSON.parse(data);
                if(Number(resp['status']) !== 200 )
                    alert(resp['message']);
                else
                    document.location.href = "/cart/checkout/success/" + resp['orderId'];

            },
            error:console.error
        });
    });
});