<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['submit_edit_upload'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_POST['id_tugas']);
    $id_akun = mysqli_real_escape_string($koneksi, $_POST['id_akun']);
    $old_file = mysqli_real_escape_string($koneksi, $_POST['old_file']);

    $target_dir = "../uploads/";
    $new_file_name = '';
    $uploadOk = 1;

    if (!empty($_FILES["file_tugas_edit"]["name"])) {
        $original_file_name = basename($_FILES["file_tugas_edit"]["name"]);
        $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . '_' . $original_file_name;
        $target_file = $target_dir . $new_file_name;

        if ($file_extension != "docx" && $file_extension != "pdf") {
            echo "<script>alert('Maaf, hanya file DOCX dan PDF yang diperbolehkan.'); window.location.href='index.php';</script>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (file_exists($target_dir . $old_file)) {
                unlink($target_dir . $old_file);
            }

            if (move_uploaded_file($_FILES["file_tugas_edit"]["tmp_name"], $target_file)) {
                $update_sql = "UPDATE pengumpulan SET nama_file = '$new_file_name', tanggal_upload = NOW() WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
                if (mysqli_query($koneksi, $update_sql)) {
                    echo "<script>alert('Tugas berhasil diperbarui!'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $update_sql . "\\n" . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
                }
            } else {
                echo "<script>alert('Maaf, terjadi kesalahan saat mengganti file Anda.'); window.location.href='index.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Tidak ada file baru diunggah. Tidak ada perubahan.'); window.location.href='index.php';</script>";
    }
} else {
    header("Location: index.php");
    exit();
}
mysqli_close($koneksi);
?>