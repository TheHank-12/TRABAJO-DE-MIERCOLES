

// FUNCIONALIDAD: Vanilla JS puro para Previsualizaciones, Modales, Cálculos

document.addEventListener("DOMContentLoaded", function () {
  // 1. Alternar Menú Responsivo (Móvil/Tablet)
  const btnMenuToggle = document.getElementById("btn-menu-toggle");
  const navMenu = document.getElementById("nav-menu-main");

  if (btnMenuToggle && navMenu) {
    btnMenuToggle.addEventListener("click", function () {
      navMenu.classList.toggle("active");
    });
  }

  // 2. Previsualización de Imagen Portada en Tiempo Real
  const inputPortada = document.getElementById("input-portada");
  const imgPreview = document.getElementById("img-preview");

  if (inputPortada && imgPreview) {
    inputPortada.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          imgPreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // 3. Filtro Búsqueda Dinámica en Tiempo Real (Tablas y Catálogo)
  const inputSearch = document.getElementById("input-search-live");
  if (inputSearch) {
    inputSearch.addEventListener("keyup", function () {
      const filter = this.value.toLowerCase();

      // Filtrar tarjetas de catálogo si existen
      const tarjetas = document.querySelectorAll(".card-libro");
      tarjetas.forEach(function (card) {
        const titulo = card.querySelector(".card-titulo")
          ? card.querySelector(".card-titulo").innerText.toLowerCase()
          : "";
        const autor = card.querySelector(".card-autor")
          ? card.querySelector(".card-autor").innerText.toLowerCase()
          : "";

        if (titulo.includes(filter) || autor.includes(filter)) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });

      // Filtrar filas de tabla si existen
      const filasTabla = document.querySelectorAll(".tabla-custom tbody tr");
      filasTabla.forEach(function (row) {
        const texto = row.innerText.toLowerCase();
        if (texto.includes(filter)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  }

  // 4. Cálculo Dinámico de Alquiler y Fecha de Devolución
  const fechaInicioInput = document.getElementById("fecha_inicio");
  const diasAlquilerInput = document.getElementById("dias_alquiler");
  const precioDiarioInput = document.getElementById("precio_alquiler_diario");
  const fechaDevEsperadaText = document.getElementById("fecha_dev_esperada_preview");
  const totalAlquilerText = document.getElementById("total_alquiler_preview");

  function calcularAlquiler() {
    if (fechaInicioInput && diasAlquilerInput && precioDiarioInput) {
      const fechaInicio = new Date(fechaInicioInput.value);
      const dias = parseInt(diasAlquilerInput.value) || 0;
      const precioDiario = parseFloat(precioDiarioInput.value) || 0;

      if (!isNaN(fechaInicio.getTime()) && dias > 0) {
        const fechaDev = new Date(fechaInicio);
        fechaDev.setDate(fechaDev.getDate() + dias);

        const yyyy = fechaDev.getFullYear();
        const mm = String(fechaDev.getMonth() + 1).padStart(2, "0");
        const dd = String(fechaDev.getDate()).padStart(2, "0");

        if (fechaDevEsperadaText) {
          fechaDevEsperadaText.innerText = `${yyyy}-${mm}-${dd}`;
        }

        const total = (dias * precioDiario).toFixed(2);
        if (totalAlquilerText) {
          totalAlquilerText.innerText = `S/ ${total}`;
        }
      }
    }
  }

  if (fechaInicioInput) fechaInicioInput.addEventListener("change", calcularAlquiler);
  if (diasAlquilerInput) diasAlquilerInput.addEventListener("input", calcularAlquiler);

  // 5. Gestión del Selector Interactivo de Estrellas para Reseñas
  const starLabels = document.querySelectorAll(".star-rating label");
  const inputPuntuacion = document.getElementById("puntuacion_val");

  starLabels.forEach((star, index) => {
    star.addEventListener("click", () => {
      const val = star.getAttribute("data-value");
      if (inputPuntuacion) inputPuntuacion.value = val;
    });
  });

  // 6. Modal Interactivo Global de Confirmación
  window.confirmarAccion = function (url, mensaje) {
    const modal = document.getElementById("modal-confirmacion");
    const modalTexto = document.getElementById("modal-texto-confirmacion");
    const btnAceptar = document.getElementById("modal-btn-aceptar");

    if (modal && modalTexto && btnAceptar) {
      modalTexto.innerText = mensaje || "¿Está seguro de realizar esta acción?";
      btnAceptar.href = url;
      modal.style.display = "flex";
    } else {
      if (confirm(mensaje)) {
        window.location.href = url;
      }
    }
  };

  window.cerrarModal = function () {
    const modal = document.getElementById("modal-confirmacion");
    if (modal) modal.style.display = "none";
  };
});


// GESTIÓN DE MODALES FLOTANTES DE PAGO (YAPE, PLIN, TARJETA)


document.addEventListener("DOMContentLoaded", function () {
  const selectMetodoPago = document.getElementById("metodo_pago");
  const hiddenNumOp = document.getElementById("num_operacion_hidden");
  const resumenPago = document.getElementById("resumen-pago-seleccionado");
  const formCheckout = document.getElementById("form-checkout");

  if (!selectMetodoPago) return;

  // Escuchar cambio en el selector de Método de Pago
  selectMetodoPago.addEventListener("change", function () {
    const metodo = this.value;

    if (metodo === "YAPE" || metodo === "PLIN") {
      abrirModalDigital(metodo);
    } else if (metodo === "TARJETA") {
      abrirModalTarjeta();
    } else if (metodo === "TRANSFERENCIA") {
      const ref = prompt("Ingrese el número de transferencia bancaria:");
      if (ref && ref.trim() !== "") {
        hiddenNumOp.value = "TR-" + ref.trim();
        if (resumenPago) resumenPago.innerText = "✓ Transferencia registrada: " + hiddenNumOp.value;
      } else {
        selectMetodoPago.value = "EFECTIVO";
        hiddenNumOp.value = "";
        if (resumenPago) resumenPago.innerText = "";
      }
    } else {
      hiddenNumOp.value = "";
      if (resumenPago) resumenPago.innerText = "";
    }
  });

  // Formateador automático de tarjeta (agrega espacios cada 4 dígitos)
  const cardInput = document.getElementById("card-number");
  if (cardInput) {
    cardInput.addEventListener("input", function (e) {
      let val = e.target.value.replace(/\D/g, "");
      val = val.replace(/(.{4})/g, "$1 ").trim();
      e.target.value = val.substring(0, 19);
    });
  }

  // Formateador de Fecha Exp (MM/AA)
  const expInput = document.getElementById("card-exp");
  if (expInput) {
    expInput.addEventListener("input", function (e) {
      let val = e.target.value.replace(/\D/g, "");
      if (val.length >= 2) {
        val = val.substring(0, 2) + "/" + val.substring(2, 4);
      }
      e.target.value = val.substring(0, 5);
    });
  }

  // Validación previa al Submit
  if (formCheckout) {
    formCheckout.addEventListener("submit", function (e) {
      const metodo = selectMetodoPago.value;
      if ((metodo === "YAPE" || metodo === "PLIN" || metodo === "TARJETA" || metodo === "TRANSFERENCIA") && !hiddenNumOp.value) {
        e.preventDefault();
        alert("Debe completar los datos de confirmación de pago para " + metodo + ".");
        if (metodo === "YAPE" || metodo === "PLIN") abrirModalDigital(metodo);
        if (metodo === "TARJETA") abrirModalTarjeta();
      }
    });
  }
});

// Funciones Globales para Abrir y Cerrar Modales
window.abrirModalDigital = function (metodo) {
  const modal = document.getElementById("modal-pago-digital");
  const titulo = document.getElementById("modal-digital-titulo");
  const imgQr = document.getElementById("modal-qr-img");
  const numOp = document.getElementById("input-num-op-digital");

  if (modal && titulo && imgQr) {
    titulo.innerText = "Pago con " + metodo;
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
    imgQr.src = (metodo === "YAPE") ? baseUrl + "img/qr_yape.png" : baseUrl + "img/qr_plin.png";
    if (numOp) numOp.value = "";
    modal.style.display = "flex";
  }
};

window.abrirModalTarjeta = function () {
  const modal = document.getElementById("modal-pago-tarjeta");
  if (modal) modal.style.display = "flex";
};

window.cerrarModalPago = function (modalId) {
  const modal = document.getElementById(modalId);
  const selectMetodoPago = document.getElementById("metodo_pago");
  const hiddenNumOp = document.getElementById("num_operacion_hidden");
  
  if (modal) modal.style.display = "none";
  if (!hiddenNumOp.value && selectMetodoPago) {
    selectMetodoPago.value = "EFECTIVO";
  }
};

window.confirmarPagoDigital = function () {
  const numOp = document.getElementById("input-num-op-digital").value.trim();
  const selectMetodo = document.getElementById("metodo_pago").value;
  const hiddenNumOp = document.getElementById("num_operacion_hidden");
  const resumenPago = document.getElementById("resumen-pago-seleccionado");

  if (numOp.length < 4) {
    alert("Por favor ingrese un número de operación válido (mínimo 4 caracteres).");
    return;
  }

  const prefix = (selectMetodo === "YAPE") ? "YP-" : "PL-";
  hiddenNumOp.value = prefix + numOp;
  if (resumenPago) resumenPago.innerText = "✓ Pago " + selectMetodo + " confirmado: " + hiddenNumOp.value;

  document.getElementById("modal-pago-digital").style.display = "none";
};

window.confirmarPagoTarjeta = function () {
  const cardNum = document.getElementById("card-number").value.replace(/\s/g, "");
  const cardHolder = document.getElementById("card-holder").value.trim();
  const cardExp = document.getElementById("card-exp").value.trim();
  const cardCvv = document.getElementById("card-cvv").value.trim();

  if (cardNum.length < 15) {
    alert("Ingrese un número de tarjeta válido de 16 dígitos.");
    return;
  }
  if (!cardHolder) {
    alert("Ingrese el nombre del titular de la tarjeta.");
    return;
  }
  if (cardExp.length < 5) {
    alert("Ingrese la fecha de expiración en formato MM/AA.");
    return;
  }
  if (cardCvv.length < 3) {
    alert("Ingrese el código CVV de 3 dígitos.");
    return;
  }

  const hiddenNumOp = document.getElementById("num_operacion_hidden");
  const resumenPago = document.getElementById("resumen-pago-seleccionado");

  // Generar un código de aprobación simulado tipo pasarela (ej. VISA-849201)
  const last4 = cardNum.slice(-4);
  const authCode = "TC-****" + last4 + "-" + Math.floor(1000 + Math.random() * 9000);
  
  hiddenNumOp.value = authCode;
  if (resumenPago) resumenPago.innerText = "✓ Tarjeta Aprobada (" + authCode + ")";

  document.getElementById("modal-pago-tarjeta").style.display = "none";
};