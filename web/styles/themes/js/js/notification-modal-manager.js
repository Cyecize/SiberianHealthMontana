$(function () {
    var notificationIcon = document.getElementById('notification-icon');

    var NotificationModalManager = (function () {
        var notificationBar = document.getElementById('hidden-noti-bar');

        //private methods
        function hasClickedOnParentChild(children, event) {
            var isCl = false;
            for (var i = 0; i < children.length; i++) {
                if (children[i] == event.target)
                    isCl = true;
            }
            return isCl;
        }


        function fillNotificationBarWithData(htmlData) {
            var a = $(notificationBar).text().trim();
            var b = $(htmlData).text().trim();
            if (a.localeCompare(b) !== 0)
                $(notificationBar).html(htmlData);
            attachDynamicEvents();
        }

        function removeNotification(id) {
            $.ajax({
                type: "POST",
                url: "/user/notifications/remove",
                data: {notificationId: id},
                success: fillNotificationBarWithData,
                error: console.error
            });
        }

        function attachDynamicEvents() {
            $('.removeAllNotificationsBtn').on('click', NotificationModalManager.removeAllNotifications);
            $('.remove-noti-btn').on('click', function (event) {
                event.preventDefault();
                var id = $(this).attr('notificationId');
                removeNotification(id);
            });
        }


        //public methods
        function showOrHideForm() {
            if(window.innerWidth < 768){
                location.href = "/user/notifications/mobile";
                return;
            }
            if ($(notificationBar).css('display') == "none")
                $(notificationBar).show(100);
            else
                $(notificationBar).hide(100);
        }

        function onDocumentClick(event) {
            if (event.target !== notificationBar && event.target !== notificationIcon && !hasClickedOnParentChild(notificationBar.getElementsByTagName('*'), event))
                $(notificationBar).hide(100);
        }

        function update() {
            $.ajax({
                type: "POST",
                url: "/user/notifications/request",
                success: fillNotificationBarWithData,
                error: console.error
            });
        }

        function removeAllNotifications(event) {
            $.ajax({
                type: "POST",
                url: "/user/notifications/remove-all",
                success: function (data) {
                    if(event.target.id == "removeAllNotisMobileBtn")
                        location.reload();
                    fillNotificationBarWithData(data);
                },
                error: console.error
            });
        }

        return {
            showOrHideForm: showOrHideForm,
            onDocumentClick: onDocumentClick,
            update: update,
            removeAllNotifications: removeAllNotifications
        };
    })();


    $(notificationIcon).on('click', NotificationModalManager.showOrHideForm);
    document.addEventListener('click', NotificationModalManager.onDocumentClick);
    NotificationModalManager.update();
    var clock = setInterval(NotificationModalManager.update, 4000);

});