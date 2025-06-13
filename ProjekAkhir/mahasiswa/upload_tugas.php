<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['submit_upload'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_POST['id_tugas']);
    $id_akun = mysqli_real_escape_string($koneksi, $_POST['id_akun']);

    $target_dir = "../uploads/";
    $original_file_name = basename($_FILES["file_tugas"]["name"]);
    $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $unique_file_name = uniqid() . '_' . $original_file_name;
    $target_file = $target_dir . $unique_file_name;
    $uploadOk = 1;

    if ($file_extension != "docx" && $file_extension != "pdf") {
        echo "<script>alert('Maaf, hanya file DOCX dan PDF yang diperbolehkan.'); window.location.href='index.php';</script>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "<script>alert('File gagal diunggah.'); window.location.href='index.php';</script>";
    } else {
        if (move_uploaded_file($_FILES["file_tugas"]["tmp_name"], $target_file)) {
            $check_sql = "SELECT id_pengumpulan FROM pengumpulan WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
            $check_result = mysqli_query($koneksi, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                $row = mysqli_fetch_assoc($check_result);
                $id_pengumpulan = $row['id_pengumpulan'];
                $update_sql = "UPDATE pengumpulan SET nama_file = '$unique_file_name', tanggal_upload = NOW() WHERE id_pengumpulan = '$id_pengumpulan'";
                if (mysqli_query($koneksi, $update_sql)) {
                    echo "<script>alert('Tugas berhasil diperbarui!'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $update_sql . "\\n" . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
                }
            } else {
                $insert_sql = "INSERT INTO pengumpulan (id_tugas, id_akun, nama_file, tanggal_upload) VALUES ('$id_tugas', '$id_akun', '$unique_file_name', NOW())";
                if (mysqli_query($koneksi, $insert_sql)) {
                    echo "<script>alert('Tugas berhasil diunggah!'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $insert_sql . "\\n" . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
                }
            }
        } else {
            echo "<script>alert('Maaf, terjadi kesalahan saat mengunggah file Anda.'); window.location.href='index.php';</script>";
        }
    }
} else {
    header("Location: index.php");
    exit();
}
mysqli_close($koneksi);
?>