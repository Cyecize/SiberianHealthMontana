var ModalManager = function (modalId) {
    var modal = null;

    if(modalId !== undefined)
        initialize(modalId);

    function initialize(modalId) {
        var name = "#" + modalId;
        modal = $(name);
        modal.find('.close').on('click',function () {
            hideModal();
        });

    }

    function showModal() {
        $(modal).show();
    }

    function hideModal() {
        $(modal).hide();
    }

    function clearFields() {
        $(modal).find('input').trigger('reset');
    }

    return{
        initialize, showModal, clearFields, hideModal
    }
};