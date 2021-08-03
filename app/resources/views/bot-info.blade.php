<html lang="ru">
<body>
<h4>Bot info</h4>
@foreach ($botInfo as $key => $info)
    <div>
        <span style="font-weight: bold">{{ $key }}</span> -
        <span>{{ $info }}</span>
    </div>
@endforeach

<h4>Error bot info</h4>
@foreach ($errorBotInfo as $key => $info)
    <div>
        <span style="font-weight: bold">{{ $key }}</span> -
        <span>{{ $info }}</span>
    </div>
@endforeach

<h4>Bot webhook info</h4>
@foreach ($webhookInfo as $key => $info)
    <div>
        <span style="font-weight: bold">{{ $key }}</span> -
        <span>{{ $info }}</span>
    </div>
@endforeach

<h4>Error bot webhook info</h4>
@foreach ($errorWebhookInfo as $key => $info)
    <div>
        <span style="font-weight: bold">{{ $key }}</span> -
        <span>{{ $info }}</span>
    </div>
@endforeach
</body>
</html>
