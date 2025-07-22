class SalesSystem {
  constructor() {
    this.cart = JSON.parse(localStorage.getItem("apotech_cart")) || [];
    this.initElements();
    this.initEvents();
    this.renderCart();
  }

  initElements() {
    this.$drugSelect = document.getElementById("drugSelect");
    this.$quantity = document.getElementById("quantity");
    this.$addItem = document.getElementById("addItem");
    this.$clearCart = document.getElementById("clearCart");
    this.$cartTable = document.getElementById("cartTable");
    this.$cartBody = this.$cartTable.querySelector("tbody");
    this.$checkoutForm = document.getElementById("checkoutForm");
    this.$totalDisplay = document.getElementById("displayTotal");
  }

  initEvents() {
    this.$addItem.addEventListener("click", () => this.addToCart());
    this.$clearCart.addEventListener("click", () => this.clearCart());
    this.$checkoutForm.addEventListener("submit", (e) => this.checkout(e));

    // Enable keyboard add on quantity field
    this.$quantity.addEventListener("keypress", (e) => {
      if (e.key === "Enter") this.addToCart();
    });
  }

  addToCart() {
    const selectedDrug =
      this.$drugSelect.options[this.$drugSelect.selectedIndex];

    if (!selectedDrug.value) {
      alert("Pilih obat terlebih dahulu!");
      return;
    }

    const quantity = parseInt(this.$quantity.value);
    if (isNaN(quantity) || quantity <= 0) {
      alert("Jumlah harus angka positif!");
      return;
    }

    const drugId = selectedDrug.value;
    const drugName = selectedDrug.dataset.name;
    const drugPrice = parseFloat(selectedDrug.dataset.price);
    const drugCode = selectedDrug.dataset.code;
    const drugStock = parseInt(selectedDrug.dataset.stock);

    if (quantity > drugStock) {
      alert(`Stok tidak mencukupi! Stok tersedia: ${drugStock}`);
      return;
    }

    // Check if already in cart
    const existingItem = this.cart.find((item) => item.id === drugId);
    if (existingItem) {
      existingItem.qty += quantity;
      if (existingItem.qty > drugStock) {
        alert(`Total melebihi stok! Maksimal: ${drugStock}`);
        return;
      }
    } else {
      this.cart.push({
        id: drugId,
        name: drugName,
        code: drugCode,
        price: drugPrice,
        qty: quantity,
      });
    }

    this.saveCart();
    this.renderCart();
    this.$quantity.value = 1;
  }

  renderCart() {
    this.$cartBody.innerHTML = "";
    let total = 0;

    this.cart.forEach((item, index) => {
      const subtotal = item.price * item.qty;
      total += subtotal;

      const row = document.createElement("tr");
      row.dataset.id = item.id;
      row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.code || "-"}</td>
                <td class="price">Rp ${item.price.toLocaleString()}</td>
                <td>
                    <input type="number" 
                           class="form-control qty-input" 
                           value="${item.qty}" 
                           min="1" 
                           max="1000"
                           data-index="${index}">
                </td>
                <td class="subtotal">Rp ${subtotal.toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
      this.$cartBody.appendChild(row);
    });

    // Update total display
    this.$totalDisplay.value = `Rp ${total.toLocaleString()}`;
    document.getElementById(
      "totalAmount"
    ).textContent = `Rp ${total.toLocaleString()}`;
    document.getElementById("totalInput").value = total;

    // Add event listeners to new elements
    this.$cartBody.querySelectorAll(".qty-input").forEach((input) => {
      input.addEventListener("change", (e) => this.updateQuantity(e));
    });

    this.$cartBody.querySelectorAll(".remove-item").forEach((btn) => {
      btn.addEventListener("click", (e) => this.removeItem(e));
    });
  }

  updateQuantity(e) {
    const index = e.target.dataset.index;
    const newQty = parseInt(e.target.value);

    if (isNaN(newQty)) {
      e.target.value = this.cart[index].qty;
      return;
    }

    // Validate against stock
    const drugId = this.cart[index].id;
    const selectedDrug = [...this.$drugSelect.options].find(
      (opt) => opt.value === drugId
    );
    const maxStock = parseInt(selectedDrug.dataset.stock);

    if (newQty > maxStock) {
      alert(`Stok tidak mencukupi! Maksimal: ${maxStock}`);
      e.target.value = this.cart[index].qty;
      return;
    }

    this.cart[index].qty = newQty;
    this.saveCart();
    this.renderCart();
  }

  removeItem(e) {
    const index = e.target.closest("button").dataset.index;
    this.cart.splice(index, 1);
    this.saveCart();
    this.renderCart();
  }

  clearCart() {
    if (confirm("Yakin ingin mengosongkan keranjang?")) {
      this.cart = [];
      this.saveCart();
      this.renderCart();
    }
  }

  saveCart() {
    localStorage.setItem("apotech_cart", JSON.stringify(this.cart));
  }

  checkout(e) {
    e.preventDefault();

    try {
      // Validate cart
      if (this.cart.length === 0) {
        throw new Error("Keranjang belanja kosong!");
      }

      // Prepare items data
      const items = this.cart.map((item) => ({
        obat_id: item.id,
        qty: item.qty,
        harga: item.price,
      }));

      // Set form values
      document.getElementById("itemsInput").value = JSON.stringify(items);
      document.getElementById("totalInput").value = this.calculateTotal();

      // Submit form
      this.$checkoutForm.submit();
    } catch (error) {
      alert(`Gagal memproses pembayaran: ${error.message}`);
      console.error("Checkout Error:", error);
    }
  }

  calculateTotal() {
    return this.cart.reduce((total, item) => total + item.price * item.qty, 0);
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  // Check for emergency fallback
  if (localStorage.getItem("emergency_cart")) {
    const warningDiv = document.createElement("div");
    warningDiv.className = "alert alert-warning fixed-bottom m-3";
    warningDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Mode Darurat</strong> Sistem sedang menggunakan fallback.
        `;
    document.body.appendChild(warningDiv);
  }

  // Main system initialization
  try {
    if (document.getElementById("drugSelect")) {
      window.SalesSystem = new SalesSystem();
    }
  } catch (error) {
    console.error("System Initialization Error:", error);
    alert("Gagal memuat sistem. Silakan refresh halaman.");
  }
});
