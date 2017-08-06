$(document).ready(function() {
    function sendMessage() {
        var text = $('#message-text').val();
        if (text === '') {
            return;
        }
        var params = {
            'text' : text
        };
        $('#message-text').val('');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: sendPath,
            data: params
        }).done(function(msg){
            if (msg === false) {
                $('#messages-box').append('<div class="message-error">An error occurred while sending message.</div>');
            } else {
                var d = createDate();
                $('#messages-box').append(
                    '<div class="message"><span class="date">('
                    + d +
                     ')</span> <span class="' + self.role + ' text-bold">' + self.username + ':</span> '
                    + text + '</div>'
                );
            }
        });
    }

    function refreshChat()
    {
        $.ajax({
            dataType: "json",
            url: refreshPath
        }).done(function(msg){
            $.each( msg, function( key, val ) {
                createNewMessage(val);
                console.log(val);
            });
        });
        setTimeout(refreshChat, 2000);
    }
    setTimeout(refreshChat, 2000);
    //sending new message when clicked on button
    $('body').on('click', '#send', function(){
        sendMessage();
    });

    //sending new message when pressed enter
    $('body').on('keypress', '#message-text' , function( event ) {
        if (event.which == 13 ) {
            sendMessage();
        }
    });

    function createDate(dateInput)
    {
        if (date !== undefined) {
            var d = new Date(dateInput);
        } else {
            var d = new Date();
        }
        var date = '';
        if (d.getHours() < 10) {
            date += '0' + d.getHours() + ':';
        } else {
            date += d.getHours() + ':';
        }
        if (d.getMinutes() < 10) {
            date += '0' + d.getMinutes() + ':';
        } else {
            date += d.getMinutes() + ':';
        }
        if (d.getSeconds() < 10) {
            date += '0' + d.getSeconds();
        } else {
            date += d.getSeconds();
        }
        return date;
    }

    function createNewMessage(val)
    {
        var d = createDate(val.date);
        $('#messages-box').append(
            '<div class="message"><span class="date">('
            + d +
            ')</span> <span class="' + val.user_role + ' text-bold">' + val.username + ':</span> '
            + val.text + '</div>'
        );
    }
});