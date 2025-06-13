<?php
session_start();
include '../config/koneksi.php';

if (isset($_GET['id_tugas'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);

    mysqli_begin_transaction($koneksi);

    try {
        $query_delete_nilai = "DELETE FROM nilai WHERE id_tugas = '$id_tugas'";
        if (!mysqli_query($koneksi, $query_delete_nilai)) {
            throw new Exception("Error menghapus data nilai: " . mysqli_error($koneksi));
        }
        $query_get_files = "SELECT nama_file FROM pengumpulan WHERE id_tugas = '$id_tugas'";
        $result_files = mysqli_query($koneksi, $query_get_files);
        if ($result_files) {
            while ($row_file = mysqli_fetch_assoc($result_files)) {
                $file_path = '../uploads/' . $row_file['nama_file'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        $query_delete_pengumpulan = "DELETE FROM pengumpulan WHERE id_tugas = '$id_tugas'";
        if (!mysqli_query($koneksi, $query_delete_pengumpulan)) {
            throw new Exception("Error menghapus data pengumpulan: " . mysqli_error($koneksi));
        }
        $query_get_admin_file = "SELECT nama_file_tugas_admin FROM tugas WHERE id_tugas = '$id_tugas'";
        $result_admin_file = mysqli_query($koneksi, $query_get_admin_file);
        if ($result_admin_file && mysqli_num_rows($result_admin_file) > 0) {
            $row_admin_file = mysqli_fetch_assoc($result_admin_file);
            $admin_file_path = '../uploads/tugas_admin/' . $row_admin_file['nama_file_tugas_admin'];
            if (!empty($row_admin_file['nama_file_tugas_admin']) && file_exists($admin_file_path)) {
                unlink($admin_file_path);
            }
        }

        $query_delete_tugas = "DELETE FROM tugas WHERE id_tugas = '$id_tugas'";
        if (!mysqli_query($koneksi, $query_delete_tugas)) {
            throw new Exception("Error menghapus tugas: " . mysqli_error($koneksi));
        }

        mysqli_commit($koneksi);
        header("Location: admin_dashboard.php?status=deleted"); 
        exit();

    } catch (Exception $e) {
        mysqli_rollback($koneksi);

        header("Location: admin_dashboard.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    header("Location: admin_dashboard.php");
    exit();
}

mysqli_close($koneksi);
?>