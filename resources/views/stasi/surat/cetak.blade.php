<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar Pencairan Bansos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --dark: #0F172A;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-700: #334155;
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        
        @page { size: A4; margin: 0; }
        
        body { 
            font-family: "Times New Roman", Times, serif; 
            margin: 0; 
            padding: 0; 
            background: #E2E8F0;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        
        /* Modern UI Print Toolbar (Screen Only) */
        .print-toolbar { 
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            background: white; padding: 1rem 2rem; border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-md); font-family: 'Inter', sans-serif;
        }
        .toolbar-title { font-size: 1.1rem; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 0.5rem; }
        .toolbar-actions { display: flex; gap: 1rem; }
        
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600; font-family: 'Inter', sans-serif;
            border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2); }
        .btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3); }
        .btn-secondary { background: white; color: var(--gray-700); border: 1px solid var(--gray-200); }
        .btn-secondary:hover { background: var(--gray-100); color: var(--dark); }

        /* The A4 Paper Page */
        .page { 
            width: 21cm; 
            min-height: 29.7cm; 
            padding: 2.5cm; 
            margin: 2rem auto; 
            background: #fff; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
            box-sizing: border-box;
            position: relative;
        }
        
        @media print {
            body { background: #fff; margin: 0; -webkit-print-color-adjust: exact; }
            .print-toolbar { display: none !important; }
            .page { margin: 0; box-shadow: none; padding: 2cm; min-height: 100vh; }
        }
        
        /* Kop Surat Official */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
            margin-bottom: 30px;
            position: relative;
        }
        .kop-surat h1 { margin: 0 0 4px 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .kop-surat h2 { margin: 0 0 4px 0; font-size: 18pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-surat p { margin: 0; font-size: 10.5pt; }
        
        /* Letter Meta Info */
        .meta-surat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .meta-kiri table { width: auto; border-collapse: collapse; }
        .meta-kiri td { padding: 2px 8px 2px 0; vertical-align: top; }
        .meta-kanan { text-align: right; }

        /* Recipient */
        .tujuan { margin-bottom: 30px; line-height: 1.6; }

        /* Content */
        .isi-surat { text-align: justify; margin-bottom: 40px; line-height: 1.6; }
        .isi-surat p { margin-bottom: 15px; text-indent: 50px; }

        /* Signature block */
        .tanda-tangan {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            text-align: center;
            width: 280px;
        }
        .ttd-nama {
            margin-top: 90px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="print-toolbar no-print">
        <div class="toolbar-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Preview Surat Pengantar
        </div>
        <div class="toolbar-actions">
            <button class="btn btn-secondary" onclick="window.close()">Tutup Preview</button>
            <button class="btn btn-primary" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Surat Sekarang
            </button>
        </div>
    </div>

    <div class="page">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <h1>Keuskupan {{ $paroki?->keuskupan ?? 'Agung Jakarta' }}</h1>
            <h2>Paroki {{ $paroki?->name ?? '...........................' }}</h2>
            <h2>Stasi {{ $stasi?->name ?? '...........................' }}</h2>
            <p>Alamat: {{ $stasi?->address ?? '........................................................................' }}</p>
        </div>

        <!-- META SURAT -->
        <div class="meta-surat">
            <div class="meta-kiri">
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td>{{ $report->letter_number ?? '___/SPB/ST/'.date('m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>:</td>
                        <td>1 (Satu) Berkas Rekapitulasi Calon</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td><b>Permohonan Validasi Paroki & Pencairan Dana Bansos</b></td>
                    </tr>
                </table>
            </div>
            <div class="meta-kanan">
                {{ $stasi?->city ?? 'Kota/Kabupaten' }}, {{ \Carbon\Carbon::parse($tanggalSurat ?? now())->translatedFormat('d F Y') }}
            </div>
        </div>

        <!-- TUJUAN -->
        <div class="tujuan">
            Kepada Yth.,<br>
            <b>Pastor Kepala / Tim Karitatif Paroki</b><br>
            Paroki {{ $paroki?->name ?? '...........................' }}<br>
            di Tempat
        </div>

        <!-- ISI SURAT -->
        <div class="isi-surat">
            <p>Dengan hormat,</p>
            <p>Semoga kasih karunia Tuhan senantiasa menyertai kita semua dalam pelayanan dan kehidupan sehari-hari.</p>
            <p>Bersama surat ini, kami selaku Pengurus Stasi {{ $stasi?->name ?? '...........................' }} bermaksud mengajukan permohonan validasi lanjutan (perankingan) dan pencairan dana Bantuan Sosial (Bansos) bagi umat yang membutuhkan di wilayah Stasi kami. Pengajuan ini telah melalui proses verifikasi dan validasi secara bertahap sesuai dengan prosedur yang telah ditetapkan oleh Tim Karitatif Paroki.</p>
            <p>Adapun jumlah keluarga atau calon penerima yang telah kami validasi, setujui, dan kami usulkan dari Stasi {{ $stasi?->name ?? '...........................' }} adalah sebanyak <b>{{ $calonCount ?? 0 }}</b> Kepala Keluarga/Individu. Berkas rekapitulasi data calon penerima terkait permohonan tersebut telah tercatat secara sistem dan terlampir untuk diperiksa lebih lanjut oleh pihak Paroki.</p>
            <p>Kami sangat berharap permohonan ini dapat segera diproses, mengingat kondisi beberapa keluarga yang sangat membutuhkan uluran tangan dan bantuan tersebut pada saat ini.</p>
            <p>Demikian surat pengantar dan permohonan ini kami sampaikan. Atas perhatian, kebijaksanaan, serta kerja sama dari Pastor Kepala dan Tim Karitatif Paroki, kami mengucapkan banyak terima kasih.</p>
        </div>

        <!-- TANDA TANGAN -->
        <div class="tanda-tangan">
            <div class="ttd-box">
                <div>Hormat kami,</div>
                <div style="margin-top: 5px;">Ketua Stasi {{ $stasi?->name ?? '...........................' }}</div>
                
                <div class="ttd-nama">
                    {{ $stasi?->leader_name ?? '( ...................................................... )' }}
                </div>
            </div>
        </div>

    </div>

</body>
</html>
