<?php
session_start();
include '../config/koneksi.php';

if (isset($_GET['id_tugas']) && isset($_GET['id_akun'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);
    $id_akun = mysqli_real_escape_string($koneksi, $_GET['id_akun']);

    $query_file = "SELECT nama_file FROM pengumpulan WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
    $result_file = mysqli_query($koneksi, $query_file);
    if ($result_file && mysqli_num_rows($result_file) > 0) {
        $row_file = mysqli_fetch_assoc($result_file);
        $file_to_delete = $row_file['nama_file'];

        $delete_nilai_sql = "DELETE FROM nilai WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
        if (mysqli_query($koneksi, $delete_nilai_sql)) {
            $delete_pengumpulan_sql = "DELETE FROM pengumpulan WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
            if (mysqli_query($koneksi, $delete_pengumpulan_sql)) {
                $target_dir = "../uploads/";
                if (file_exists($target_dir . $file_to_delete)) {
                    unlink($target_dir . $file_to_delete);
                }
                echo "<script>alert('Tugas berhasil dihapus!'); window.location.href='index.php';</script>";
            } else {
                echo "<script>alert('Error menghapus data pengumpulan: " . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Error menghapus data nilai: " . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
        }
    } else {
        $delete_nilai_sql = "DELETE FROM nilai WHERE id_tugas = '$id_tugas' AND id_akun = '$id_akun'";
        if (mysqli_query($koneksi, $delete_nilai_sql)) {
             echo "<script>alert('Tugas berhasil dihapus!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error menghapus data nilai: " . mysqli_error($koneksi) . "'); window.location.href='index.php';</script>";
        }
    }
} else {
    header("Location: index.php");
    exit();
}
mysqli_close($koneksi);
?>