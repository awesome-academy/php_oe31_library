const { defaultsDeep } = require("lodash");

var url = window.location.origin;

$.ajax({
    url: url + '/notify',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
        number = 0;
        if (res.data.length != '') {
            res.data.map(function(notify) {
                number++;
                $('.notification-data').prepend(
                    `<li>
                            <a href="${url + '/request-detail/' + notify.request_id}">
                                <div class="notification-content">
                                    <i class="icon-book"></i>
                                    <span>Đơn ${notify.request_id}</span>
                                </div>
                                <b class="notification-text">${notify.content}</b>
                            </a>
                        </li>`
                );
            });
        } else {
            $('.notification-data').prepend(
                `<li>
                        <a disabled>
                             <b>Bạn hiện tại chưa có thông báo nào</b>
                        </a>
                    </li>`
            );
        }
        $('.number-notify-user').text(number);
        getNotification(number);
    },
    error: function(XHR, status, error) {

    },
    complete: function(res) {

    }
})

function getNotification(number) {
    Echo.channel('my-channel')
        .listen('NotificationUserEvent', (e) => {
            number++;
            $.ajax({
                url: url + '/notify-for-user',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log(res.user_id);
                    if (res.user_id == e.message.user_id) {
                        $('.number-notify-user').text(number);
                        $('.notification-data').prepend(
                            `<li>
                                   <a href="${url + '/request-detail/' + e.message.request_id}">
                                       <div class="notification-content">
                                           <i class="icon-book"></i>
                                           <span>Đơn ${e.message.request_id}</span>
                                       </div>
                                       <b class="notification-text">${e.message.content}</b>
                                   </a>
                               </li>`
                        );

                        $('.notification-client').append(
                            `<div class="content-message">Bạn có thông báo</div>`
                        );
                        $('.content-message').delay(2500).slideUp();
                        $('.notification-client').
                        setTimeout(function() {
                            $('.notification-client').empty();
                        }, 60000)
                    }
                },
                error: function(XHR, status, error) {

                },
                complete: function(res) {

                }
            })
        });
}