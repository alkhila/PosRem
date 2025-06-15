<style>
  #logoutConfirmModal .modal-dialog {
    max-width: 300px !important;
    width: 90% !important;
    margin: 1.75rem auto !important;
  }

  #logoutConfirmModal .modal-content {
    background-color: white !important;
    border-radius: 10px !important;
    border: 1px solid #ddd !important;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
    text-align: center !important;
    padding: 20px !important;
    height: 150px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    align-items: center !important;
    overflow: hidden !important;
  }

  #logoutConfirmModal .modal-header,
  #logoutConfirmModal .modal-footer {
    display: none !important;
  }

  #logoutConfirmModal .modal-body {
    padding: 0 !important;
    flex-grow: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
  }

  #logoutConfirmModal .modal-body p {
    color: #333 !important;
    font-size: 1rem !important;
    font-weight: normal !important;
    margin: 0 !important;
    line-height: 1.4 !important;
  }

  #logoutConfirmModal .modal-buttons-container {
    display: flex !important;
    justify-content: center !important;
    gap: 10px !important;
    width: 100% !important;
    margin-top: 15px !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn {
    border-radius: 5px !important;
    padding: 8px 15px !important;
    font-size: 0.9rem !important;
    font-weight: normal !important;
    min-width: unset !important;
    transition: background-color 0.2s ease !important;
    height: auto !important;
    flex-grow: 0 !important;
    flex-shrink: 0 !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-secondary {
    background-color: #ddd !important;
    color: #555 !important;
    border: none !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-secondary:hover {
    background-color: #ccc !important;
  }

  #logoutConfirmModal .modal-buttons-container .btn-primary {
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