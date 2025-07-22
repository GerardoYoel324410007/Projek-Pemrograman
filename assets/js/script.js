// ===== GLOBAL HELPER FUNCTIONS =====
function formatRupiah(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount);
}

// Notifikasi Toast
function showToast(type, message) {
  const toast = document.createElement("div");
  toast.className = `toast align-items-center text-white bg-${type} border-0`;
  toast.setAttribute("role", "alert");
  toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

  const toastContainer = document.getElementById("toastContainer");
  toastContainer.appendChild(toast);

  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();

  toast.addEventListener("hidden.bs.toast", () => {
    toast.remove();
  });
}

// Inisialisasi tooltip Bootstrap
function initTooltips() {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

// ===== DASHBOARD FUNCTIONS =====
function initDashboardCharts() {
  if (document.getElementById("salesChart")) {
    const ctx = document.getElementById("salesChart").getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
        datasets: [
          {
            label: "Penjualan Minggu Ini",
            data: [
              1200000, 1900000, 3000000, 2500000, 2200000, 3500000, 4000000,
            ],
            backgroundColor: "rgba(13, 110, 253, 0.2)",
            borderColor: "rgba(13, 110, 253, 1)",
            tension: 0.3,
          },
        ],
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return formatRupiah(value);
              },
            },
          },
        },
      },
    });
  }
}

// ===== DOCUMENT READY =====
document.addEventListener("DOMContentLoaded", function () {
  initTooltips();

  // Auto close alerts setelah 5 detik
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }, 5000);
  });

  // Inisialisasi chart jika ada di halaman
  if (typeof Chart !== "undefined") {
    initDashboardCharts();
  }
});
