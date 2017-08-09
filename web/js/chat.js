//source: http://dumpsite.com/forum/index.php?topic=4.msg8#msg8
String.prototype.replaceAll = function(str1, str2, ignore)
{
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
};
$(document).ready(function()
{
    var channelChanged = 0;
    var emoticonsOpened = 0;
    startChat();
    scrollMessages();
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

    $('body').on('change', '#channels', function(){
        changeChannel($(this).val());
    });

    $('.emoticon-img').click(function(){
        var value = $('#message-text').val();
        var emoticon = $(this).attr('alt');
        $('#message-text').val(value + emoticon);
    });

    $('#emoticons').click(function(){
        if (emoticonsOpened % 2) {
            hide('emoticons');
        } else {
            show('emoticons');
        }
        emoticonsOpened++;
    });

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
            if (msg.status === false) {
                $('#messages-box').append('<div class="message-error">An error occurred while sending message.</div>');
            } else {
                var d = createDate();
                $('#messages-box').append(
                    '<div class="message"><span class="date">('
                    + d +
                    ')</span> <span class="' + self.role + ' text-bold">' + self.username + ':</span> '
                    + parseMessage(text) + '</div>'
                );
            }
            if (msg.messages) {
                $.each( msg.messages, function( key, val ) {
                    createNewMessage(val);
                });
            }
            setTimeout(scrollMessages, 100);
        });
    }

    function refreshChat()
    {
        $.ajax({
            dataType: "json",
            url: refreshPath
        }).done(function(msg){
            if (msg.messages[0]) {
                $.each( msg.messages, function( key, val ) {
                    createNewMessage(val);
                });
                if (channelChanged === 0) {
                    var audio = new Audio(newMessageSound);
                    audio.currentTime = 0;
                    audio.play();
                }
                setTimeout(scrollMessages, 100);
            }
            if (msg.usersOnline) {
                $('#users-box').html('');
                $.each( msg.usersOnline, function( key, val ) {
                    createNewUser(val);
                });
            }
            if (channelChanged === 1) {
                channelChanged = 0;
            }
        });
        setTimeout(refreshChat, 2000);
    }

    function createDate(dateInput)
    {
        if (dateInput !== undefined) {
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
        var d = createDate(val.date.date);
        $('#messages-box').append(
            '<div class="message"><span class="date">('
            + d +
            ')</span> <span class="' + val.user_role + ' text-bold">' + val.username + ':</span><span class="message-text">'
            + parseMessage(val.text) + '</span></div>'
        );
    }

    function scrollMessages()
    {
        $('#messages-box').scrollTo('100%')
    }

    function createNewUser(val)
    {
        $('#users-box').append(
            '<div class="'+ val.user_role + '">' + val.username + '</div>'
        );
    }

    function changeChannel(channelId)
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: changeChannelPath,
            data: {'channel' : channelId }
        }).done(function(msg){
            if (msg == true) {
                clearChat();
                channelChanged = 1;
            }
        });
    }

    function clearChat()
    {
        $('#users-box').empty();
        $('#messages-box').empty();
    }

    function startChat()
    {
        var message = '';
        $('div.message').each(function(){
            message = $(this).children('span.message-text').text();
            $(this).children('span.message-text').html(parseMessage(message))
        });
        for(i = 0 ; i < emoticonsImg.length ; i++) {
            $('div[name="emoticons"]').append(function(){
                if (Array.isArray(emoticons[i])) {
                    alt = emoticons[i][0];
                } else {
                    alt = emoticons[i];
                }
                return '<img src="' + emoticonsImg[i] + '" class="emoticon-img kursor" alt="' + alt + '"/>';
            });
        }
    }

    function parseMessage(message)
    {
        for (i = 0; i < emoticons.length; i++) {
            if(Array.isArray(emoticons[i]) ) {
                for(j = 0 ; j < emoticons[i].length ; j++) {
                    if (message.includes(emoticons[i][j])) {
                        message = message.replaceAll(emoticons[i][j], '<img src="' + emoticonsImg[i] + '" alt="' + emoticons[i][j] + '"/>');
                    }
                }
            } else {
                if (message.includes(emoticons[i])) {
                    message = message.replaceAll(emoticons[i], '<img src="' + emoticonsImg[i] + '" alt="' + emoticons[i] + '"/>');
                }
            }
        }
        return message;
    }

    function show(id)
    {
        //x = (w_width / 2) - 250;
        //y = 300;

        $('div[name="'+id+'"]').css({  top: -35, left: 0 });
        $('div[name="'+id+'"]').fadeIn();
        //$('div[name="'+id+'"]').draggable();
    }

    function hide(id)
    {
        $('div[name="'+id+'"]').fadeOut();
    }
});