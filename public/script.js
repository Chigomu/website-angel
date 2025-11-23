// Animasi fade saat scroll
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add("visible");
    }
  });
}, { threshold: 0.2 });

document.querySelectorAll(".fade").forEach(sec => observer.observe(sec));

// Modal produk & keranjang
const modal = document.getElementById("productModal");
const modalImg = document.getElementById("modalImg");
const modalName = document.getElementById("modalName");
const modalIngredients = document.getElementById("modalIngredients");
const modalPrice = document.getElementById("modalPrice");
const addToCartBtn = document.getElementById("addToCart");
const closeBtn = document.querySelector(".close");
const cartDiv = document.getElementById("cart");
const checkoutBtn = document.getElementById("checkoutBtn");

let currentProduct = null;
let cart = [];

document.querySelectorAll(".product").forEach(prod => {
  prod.addEventListener("click", () => {
    currentProduct = {
      name: prod.dataset.name,
      price: parseInt(prod.dataset.price),
      ingredients: prod.dataset.ingredients
    };
    modalImg.src = prod.querySelector("img").src;
    modalName.textContent = currentProduct.name;
    modalIngredients.textContent = "Bahan: " + currentProduct.ingredients;
    modalPrice.textContent = "Harga: Rp " + currentProduct.price.toLocaleString();
    modal.style.display = "block";
  });
});

closeBtn.onclick = () => (modal.style.display = "none");
window.onclick = e => { if (e.target == modal) modal.style.display = "none"; };

addToCartBtn.addEventListener("click", () => {
  cart.push(currentProduct);
  updateCart();
  modal.style.display = "none";
});

function updateCart() {
  cartDiv.innerHTML = "";
  if (cart.length === 0) {
    cartDiv.innerHTML = "<p>Belum ada pesanan.</p>";
    return;
  }
  let total = 0;
  cart.forEach((item, i) => {
    total += item.price;
    const p = document.createElement("p");
    p.textContent = `${i+1}. ${item.name} - Rp ${item.price.toLocaleString()}`;
    cartDiv.appendChild(p);
  });
  const totalP = document.createElement("p");
  totalP.style.fontWeight = "bold";
  totalP.textContent = "Total: Rp " + total.toLocaleString();
  cartDiv.appendChild(totalP);
}

checkoutBtn.addEventListener("click", () => {
  if (cart.length === 0) return alert("Keranjang masih kosong!");
  let msg = "Halo Ibu Angel,%0ASaya ingin memesan:%0A";
  let total = 0;
  cart.forEach((item, i) => {
    msg += `- ${item.name} (Rp ${item.price.toLocaleString()})%0A`;
    total += item.price;
  });
  msg += `%0ATotal: Rp ${total.toLocaleString()}`;
  window.open(`https://wa.me/6281234567890?text=${msg}`, "_blank");
});
