document.addEventListener("DOMContentLoaded", function () {
  // Auto-complete pencarian obat
  const drugSearch = document.getElementById("drugSearch");
  if (drugSearch) {
    new Awesomplete(drugSearch, {
      minChars: 1,
      maxItems: 5,
      autoFirst: true,
      list: JSON.parse(drugSearch.dataset.drugs),
    });
  }

  // Validasi form tambah obat
  const drugForm = document.getElementById("drugForm");
  if (drugForm) {
    drugForm.addEventListener("submit", function (e) {
      const expiryDate = new Date(this.elements.tanggal_kadaluarsa.value);
      const today = new Date();

      if (expiryDate <= today) {
        e.preventDefault();
        showToast("danger", "Tanggal kadaluarsa tidak valid!");
        this.elements.tanggal_kadaluarsa.focus();
      }

      if (
        parseFloat(this.elements.harga_jual.value) <=
        parseFloat(this.elements.harga_beli.value)
      ) {
        e.preventDefault();
        showToast("danger", "Harga jual harus lebih besar dari harga beli!");
        this.elements.harga_jual.focus();
      }
    });
  }

  // Konfirmasi sebelum hapus obat
  const deleteButtons = document.querySelectorAll(".delete-drug");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Apakah Anda yakin ingin menghapus obat ini?")) {
        e.preventDefault();
      }
    });
  });
});
