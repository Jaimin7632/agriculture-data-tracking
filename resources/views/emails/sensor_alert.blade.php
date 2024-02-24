<!DOCTYPE html>
<html>
<head>
    <title>Sensor Alert</title>
</head>
<body>
    <h2>Hello {{ $user->name }},</h2>
    <p>The sensor value for {{ $sensorName }} has exceeded the defined thresholds:</p>
    <p>Minimum Value: {{ $minValue }}</p>
    <p>Maximum Value: {{ $maxValue }}</p>
    <p>Please take necessary action.</p>
    <p>Thank you!</p>
    <p>Regards, Your Application</p>
</body>
</html>
