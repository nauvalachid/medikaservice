<!DOCTYPE html>
<html>
<head>
    <title>Resep Medis - {{ $resep->pasien->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2980B9;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #2980B9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .image-container {
            text-align: center;
            margin-top: 20px;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            background-color: #fff;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Resep Medis</h1>
            <p>Klinik Sehat Selalu</p>
            <p>Jl. Contoh No. 123, Kota Anda</p>
        </div>

        <div class="section-title">Detail Pasien</div>
        <table>
            <tr>
                <th>Nama Pasien</th>
                <td>{{ $resep->pasien->nama_lengkap ?? $resep->pasien->nama ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $resep->pasien->user->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Nomor Telepon</th>
                <td>{{ $resep->pasien->nomor_telepon ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $resep->pasien->alamat ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">Detail Resep</div>
        <table>
            <tr>
                <th>ID Resep</th>
                <td>{{ $resep->id }}</td>
            </tr>
            <tr>
                <th>Tanggal Resep</th>
                <td>{{ $resep->created_at->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <th>Diagnosa</th>
                <td>{{ $resep->diagnosa }}</td>
            </tr>
            <tr>
                <th>Keterangan Obat</th>
                <td>{{ $resep->keterangan_obat ?? 'Tidak ada keterangan' }}</td>
            </tr>
            @if($resep->pendaftaran)
            <tr>
                <th>ID Pendaftaran</th>
                <td>{{ $resep->pendaftaran->id }}</td>
            </tr>
            <tr>
                <th>Tanggal Pendaftaran</th>
                <td>{{ $resep->pendaftaran->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endif
        </table>

        @if($resep->poto_obat_url)
        <div class="section-title">Foto Obat</div>
        <div class="image-container">
            <img src="{{ $resep->poto_obat_url }}" alt="Foto Obat">
        </div>
        @endif

        <div class="footer">
            <p>Dokumen ini dibuat secara otomatis pada {{ date('d M Y H:i') }}.</p>
            <p>&copy; {{ date('Y') }} Klinik Sehat Selalu. Semua Hak Dilindungi.</p>
        </div>
    </div>
</body>
</html>