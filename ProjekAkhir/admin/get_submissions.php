<?php
include '../config/koneksi.php';

header('Content-Type: application/json');

$response = [];

if (isset($_GET['id_tugas'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);

    $query_submissions = "SELECT
                                p.id_akun,
                                a.nama_lengkap,
                                p.nama_file,
                                p.tanggal_upload,
                                n.nilai_tugas,
                                n.komentar
                            FROM
                                pengumpulan p
                            JOIN
                                akun a ON p.id_akun = a.id_akun
                            LEFT JOIN
                                nilai n ON p.id_akun = n.id_akun AND p.id_tugas = n.id_tugas
                            WHERE
                                p.id_tugas = '$id_tugas'
                            ORDER BY
                                a.nama_lengkap ASC";
    $result_submissions = mysqli_query($koneksi, $query_submissions);

    if ($result_submissions) {
        while ($row = mysqli_fetch_assoc($result_submissions)) {
            $response[] = $row;
        }
    } else {
        echo json_encode(['error' => 'Failed to fetch data.']);
        exit();
    }
} else {
    echo json_encode(['error' => 'Missing id_tugas parameter.']);
    exit();
}

echo json_encode($response);
mysqli_close($koneksi);
?>