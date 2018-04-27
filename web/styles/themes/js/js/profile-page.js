$(function () {
    var addressModalManager = new ModalManager;
    addressModalManager.initialize("addressModal");

    var form = $('#addressForm');
    var addressModalTitle = $('#addressModalTitle');

    var userFullName = $('#userFullName');
    var userPhone = $('#userPhone');
    var userTownship = $('#userTownship');
    var userresidential = $('#userTown');
    var useraddressField = $('#userAddress');
    var userPostCode = $('#userPostCode');
    var userAddressId = $('#userAddressId');

    document.getElementById('newAddressBtn').addEventListener('click', function () {
        addressModalManager.showModal();
        addressModalManager.clearFields();
        form.attr('action', '/user/address/add');
        addressModalTitle.text('Добавяне на адрес');
    });

    $('.edit-address-btn').click(function () {
        var id = $(this).attr('addrId');
        addressModalManager.showModal();
        form.attr('action', '/user/address/edit/' + id);
        addressModalTitle.text('Редакция на адрес');

        $.ajax({
            method:"GET",
            url:"/user/address/find/"+id,
            success:function (data) {
                var result = JSON.parse(data);
                var address = result['address'];

                userPostCode.val(address['postCode']);
                useraddressField.val(address['address']);
                userresidential.val(address['residential']);
                userFullName.val(address['fullName']);
                userPhone.val(address['phoneNumber']);
                userAddressId.val(address['id']);

                userTownship.find('option').removeAttr('selected');
                userTownship.find('option').toArray().forEach(option=>{
                    var opt = $(option);
                    if(Number(opt.val()) === address['townshipId']){
                        opt.attr('selected', true);
                    }
                });

            },
            error: console.error
        })
    });

    document.addEventListener('keyup', function (ev) {
        if (ev.keyCode === 27) addressModalManager.hideModal();
    });


    //remove acc functions
    $('#removeAccountBtn').on('click',function () {
       if(confirm('Това ще изтрие вашите данни, искате ли да изтриете вашия профил?')){
           $.ajax({
               method:"GET",
               url:"/user/destroy?conf=yes",
               success:function () {
                   window.location.replace("/");
               }
           });
       }
    });
});