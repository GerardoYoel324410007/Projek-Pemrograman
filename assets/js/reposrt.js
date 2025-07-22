document.addEventListener("DOMContentLoaded", function () {
  // Date picker untuk filter laporan
  const dateInputs = document.querySelectorAll(".date-picker");
  dateInputs.forEach((input) => {
    input.flatpickr({
      dateFormat: "Y-m-d",
      allowInput: true,
    });
  });

  // Export ke Excel
  const exportExcel = document.getElementById("exportExcel");
  if (exportExcel) {
    exportExcel.addEventListener("click", function () {
      const table = document.getElementById("reportTable");
      const html = table.outerHTML;

      // Konversi ke Excel
      const blob = new Blob([html], { type: "application/vnd.ms-excel" });
      const url = URL.createObjectURL(blob);

      const a = document.createElement("a");
      a.href = url;
      a.download = "Laporan_Apotek.xls";
      a.click();

      showToast("success", "Laporan berhasil di-export!");
    });
  }
});
