<!DOCTYPE html>
<html>
<head>
    <title>Aduan</title>
</head>
<body>
    <h1>Aduan</h1>
    <p><strong>Tajuk Aduan:</strong> {{ $data['tajuk_aduan'] }}</p>
    <p><strong>Catatan Pegawai:</strong> {{ $data['catatan_pegawai'] ?? 'Tiada' }}</p>
    <p><strong>Dihantar Oleh:</strong> {{ $data['email'] }}</p>
</body>
</html>
