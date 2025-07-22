class TransactionManager {
  constructor() {
    this.itemTemplate = document.querySelector(".obat-row").cloneNode(true);
    this.container = document.getElementById("items-container");
    this.addButton = document.getElementById("add-item");
    this.totalElement = document.getElementById("total-harga");
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.addNewItem();
  }

  setupEventListeners() {
    this.addButton.addEventListener("click", () => this.addNewItem());
    this.container.addEventListener("input", (e) => this.handleInput(e));
    this.container.addEventListener("click", (e) => this.handleRemoveClick(e));
  }

  addNewItem() {
    const newItem = this.itemTemplate.cloneNode(true);
    this.resetItemForm(newItem);
    this.container.appendChild(newItem);
    newItem.querySelector(".obat-select").focus();
  }

  handleInput(e) {
    const row = e.target.closest(".obat-row");
    if (!row) return;

    if (e.target.classList.contains("obat-select")) {
      this.updateHargaFromSelect(row);
    }
    this.calculateSubtotal(row);
    this.calculateTotal();
  }

  handleRemoveClick(e) {
    if (e.target.classList.contains("hapus-item")) {
      if (document.querySelectorAll(".obat-row").length > 1) {
        e.target.closest(".obat-row").remove();
        this.calculateTotal();
      } else {
        this.resetItemForm(e.target.closest(".obat-row"));
      }
    }
  }

  updateHargaFromSelect(row) {
    const select = row.querySelector(".obat-select");
    const selectedOption = select.options[select.selectedIndex];
    const hargaInput = row.querySelector(".harga");
    hargaInput.value = selectedOption.dataset.harga || 0;
  }

  calculateSubtotal(row) {
    const jumlah = parseFloat(row.querySelector(".jumlah").value) || 0;
    const harga = parseFloat(row.querySelector(".harga").value) || 0;
    row.querySelector(".subtotal").value = (jumlah * harga).toFixed(2);
  }

  calculateTotal() {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach((input) => {
      total += parseFloat(input.value) || 0;
    });
    this.totalElement.textContent = formatRupiah(total);
    document.getElementById("total-input").value = total;
  }

  resetItemForm(item) {
    item.querySelector(".obat-select").selectedIndex = 0;
    item.querySelector(".jumlah").value = "";
    item.querySelector(".harga").value = "";
    item.querySelector(".subtotal").value = "";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new TransactionManager();
});
