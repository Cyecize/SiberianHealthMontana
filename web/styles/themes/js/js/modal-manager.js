var ModalManager = function (modalId) {
    var modal = null;
    var modalRaw  = null;

    if(modalId !== undefined)
        initialize(modalId);

    function initialize(modalId) {
        modalRaw = document.getElementById(modalId);
        modal = $(modalRaw);
        modal.find('.close').on('click',function () {
            hideModal();
        });
        modal.find('.cancel').on('click',function (e) {
            e.preventDefault();
            hideModal();
        });

        window.onclick = function(event) {
            if (event.target == modalRaw) {
                modal.hide();
            }
        }

    }

    function showModal() {
        $(modal).show();
    }

    function hideModal() {
        $(modal).hide();
    }

    function clearFields() {
        $(modal).find('form').trigger('reset');
    }

    return{
        initialize, showModal, clearFields, hideModal
    }
};