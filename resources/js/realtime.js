var url = window.location.origin;

function callApiNotification()
{
    $.ajax({
        url: url + '/admin/notification',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            var number = 0;
            var data = [];

            if (res.data.length != '') {
                res.data.map(function (list) {
                    if (list.read_at == null) {
                        number++;
                    }

                    data = JSON.parse(list.data);

                    $('.number-noti').text(number);
                    $('.number-noti-message').text(`You have ${number} notifications`);

                    $('.noti-data').prepend(
                        `<li>
                            <a href="${url + '/admin/detail-notification/' + list.id}">
                                <i class="fa fa-user text-red"></i>
                                <b> ${data.nameUser} </b>
                                ${data.content}
                            </a>
                        </li>`
                    );
                });
            }

            if (number == 0 || res.data.length == '') {
                $('.number-noti').text('');
                $('.number-noti-message').text(`You haven't notifications`);
                $('.noti-data').text('');
            }

            getNotification(number);
        },
        error: function (XHR, status, error) {

        },
        complete: function (res) {

        }
    })
}

callApiNotification();

function getNotification(number)
{
    setTimeout(function () {
        Echo.channel('channel-notification')
            .listen('NotificationEvent', (e) => {
                number++;
                $.ajax({
                    url: url + '/admin/notification-for-admin',
                    type: 'GET',
                    dataType: 'json',
                    success: function (res) {
                        var userId = res.user_id;
                        var usersId = e.message.users;
                        var checkPusher = false;

                        usersId.map(function (index) {
                            if (userId == index) {
                                checkPusher = true;
                            }
                        })

                        if (checkPusher) {
                            $('.noti-data').empty();
                            callApiNotification();
                        }
                    },
                    error: function (XHR, status, error) {

                    },
                    complete: function (res) {

                    }
                })
            });
    }, 5000);
}
