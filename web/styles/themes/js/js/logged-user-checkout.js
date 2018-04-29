$(function () {
    $('#checkoutBtn').on('click', function () {
        var selectedAddressId = null;
        var addresses = $('.actual-radio').toArray().forEach(function (el) {
            if (el.checked)
                selectedAddressId = Number(el.getAttribute('addrId').trim());
        });

        if (selectedAddressId === null || isNaN(selectedAddressId)) {
            alert('Моля изберете адрес');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/cart/checkout/commit/logged",
            data: {address: selectedAddressId},
            success: function (data) {
                var resp = JSON.parse(data);
                if (Number(resp['status']) !== 200)
                    alert(resp['message']);
                else
                    document.location.href = "/cart/checkout/success/" + resp['orderId'];
            },
            error: console.error
        });
    });
});