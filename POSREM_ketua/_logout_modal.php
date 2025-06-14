<style>
  /* Styling khusus untuk modal konfirmasi logout - DESAIN BARU, SANGAT SEDERHANA */
  #logoutConfirmModal .modal-dialog {
    max-width: 300px !important;
    /* Ukuran tetap kecil */
    width: 90% !important;
    /* Responsif */
    margin: 1.75rem auto !important;
    /* Pusatkan */
  }

  #logoutConfirmModal .modal-content {
    background-color: white !important;
    border-radius: 10px !important;
    /* Sudut lebih kecil */
    border: 1px solid #ddd !important;
    /* Border sederhana */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
    /* Shadow ringan */
    text-align: center !important;
    /* Teks di tengah */
    padding: 20px !important;
    /* Padding dalam modal */

    /* Dimensi paksa untuk stabilitas */
    height: 150px !important;
    /* Tinggi tetap */
    display: flex !important;
    /* Gunakan flex untuk alignment internal */
    flex-direction: column !important;
    /* Susun vertikal: teks di atas, tombol di bawah */
    justify-content: space-between !important;
    /* Spasi antara teks dan tombol */
    align-items: center !important;
    /* Pusatkan item secara horizontal */
    overflow: hidden !important;
    /* Pastikan tidak ada konten yang meluber */
  }

  /* Hapus styling untuk header/footer default Bootstrap jika masih ada */
  #logoutConfirmModal .modal-header,
  #logoutConfirmModal .modal-footer {
    display: none !important;
    /* Pastikan header/footer tidak muncul */
  }

  #logoutConfirmModal .modal-body {
    padding: 0 !important;
    /* Hapus padding default body */
    flex-grow: 1 !important;
    /* Biarkan body mengisi ruang kosong */
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    /* Pusatkan teks vertikal di dalam body */
    align-items: center !important;
    /* Pusatkan teks horizontal di dalam body */
  }

  #logoutConfirmModal .modal-body p {
    /* Teks konfirmasi ("Anda Yakin...", "Ingin Keluar?") */
    color: #333 !important;
    font-size: 1rem !important;
    font-weight: normal !important;
    margin: 0 !important;
    /* Hapus margin default p */
    line-height: 1.4 !important;
  }

  /* Kontainer tombol baru agar lebih mudah diatur */
  #logoutConfirmModal .modal-buttons-container {
    display: flex !important;
    justify-content: center !important;
    gap: 10px !important;
    /* Jarak antar tombol */
    width: 100% !important;
    /* Pastikan container tombol mengambil lebar penuh */
    margin-top: 15px !important;
    /* Jarak dari teks */
  }

  #logoutConfirmModal .modal-buttons-container .btn {
    /* Gaya dasar untuk semua tombol di modal ini */
    border-radius: 5px !important;
    /* Sudut tombol lebih kecil */
    padding: 8px 15px !important;
    /* Padding tombol */
    font-size: 0.9rem !important;
    font-weight: normal !important;
    min-width: unset !important;
    /* Biarkan tombol menyesuaikan konten */
    transition: background-color 0.2s ease !important;
    height: auto !important;
    /* Tinggi tombol menyesuaikan padding */
    flex-grow: 0 !important;
    /* Jangan biarkan tumbuh */
    flex-shrink: 0 !important;
    /* Jangan biarkan menyusut paksa */
  }

  #logoutConfirmModal .modal-buttons-container .btn-secondary {
    /* Tombol "Tidak" */
    background-color: #ddd !important;
    color: #555 !important;
    border: none !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-secondary:hover {
    background-color: #ccc !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-primary {
    /* Tombol "Ya" */
    background-color: #8A70D6 !important;
    color: white !important;
    border: none !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-primary:hover {
    background-color: #7b4ea3 !important;
  }
</style>

<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <p>Anda Yakin</p>
        <p>Ingin Keluar?</p>
      </div>
      <div class="modal-buttons-container">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
        <a href="logout.php" class="btn btn-primary">Ya</a>
      </div>
    </div>
  </div>
</div>