<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#17243a;line-height:1.6;">
    <h2>{{ $subjectLine }}</h2>
    <div>{!! nl2br(e($messageText)) !!}</div>
    <hr style="margin-top:24px;border:0;border-top:1px solid #ddd;">
    <p style="font-size:12px;color:#667085;"><a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}">Unsubscribe from these emails</a></p>
</body>
</html>
