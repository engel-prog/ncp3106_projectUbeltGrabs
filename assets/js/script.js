// Ubelt Grabs — fixed script (DOM ready, defensive, Bootstrap toast support, debounced search)
(function () {
  // run after DOM ready
  function ready(fn) {
    if (document.readyState !== 'loading') return fn();
    document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    const qs = id => document.getElementById(id);

    // Required IDs (we'll warn but won't fully abort so partial pages can still function)
    const requiredIds = [
      'screen-home','screen-buyer','screen-seller',
      'btnBuyer','btnSeller','buyer-back','seller-back',
      'buyerUniversity','buyerSearch','buyerGrid','buyerEmpty',
      'pUniversity','sellerForm','pName','pPrice','pCategory','pFB','pImage','pDesc',
      'sellerList','clearSeller','toast'
    ];
    const missing = requiredIds.filter(id => !qs(id));
    if (missing.length) {
      // warn but continue — this helps debug if your HTML is missing elements
      console.warn('Ubelt script: missing DOM elements (some features may not work):', missing);
    }

    // element refs (may be null — code defends)
    const screenHome = qs('screen-home');
    const screenBuyer = qs('screen-buyer');
    const screenSeller = qs('screen-seller');
    const btnBuyer = qs('btnBuyer');
    const btnSeller = qs('btnSeller');
    const buyerBack = qs('buyer-back');
    const sellerBack = qs('seller-back');
    const buyerUniversity = qs('buyerUniversity');
    const buyerSearch = qs('buyerSearch');
    const buyerGrid = qs('buyerGrid');
    const buyerEmpty = qs('buyerEmpty');
    const pUniversity = qs('pUniversity');
    const sellerForm = qs('sellerForm');
    const pName = qs('pName');
    const pPrice = qs('pPrice');
    const pCategory = qs('pCategory');
    const pFB = qs('pFB');
    const pImage = qs('pImage');
    const pDesc = qs('pDesc');
    const sellerList = qs('sellerList');
    const clearSeller = qs('clearSeller');
    const toastEl = qs('toast');

    const STORAGE_KEY = 'ubelt_products_v1';

    // Participating universities (display names are used as values here)
    const sampleUniversities = [
      'UST',
      'NU',
      'UE',
      'FEU',
      'San Beda',
      'CEU'
    ];

    // Utilities
    function saveProducts(list) {
      try { localStorage.setItem(STORAGE_KEY, JSON.stringify(list || [])); } catch (e) { console.error('saveProducts error', e); }
    }
    function loadProducts() {
      try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) { return []; }
    }
    function genId() { return Date.now().toString(36) + Math.random().toString(36).slice(2,6); }

    function escapeHtml(s) {
      if (!s && s !== 0) return '';
      return String(s).replace(/[&<>"']/g, function (c) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
      });
    }
    function escapeAttr(s) {
      if (!s && s !== 0) return '';
      return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // Bootstrap toast helper: uses bootstrap if available, otherwise in-DOM fallback
    function showToast(msg, ms = 2200) {
      if (!toastEl) {
        console.warn('toast element not found, fallback to alert:', msg);
        alert(msg);
        return;
      }
      // if bootstrap available and toast element uses '.toast', use it
      try {
        // If bootstrap's Toast constructor exists and toastEl has class 'toast'
        if (window.bootstrap && toastEl.classList.contains('toast')) {
          toastEl.querySelector('.toast-body').textContent = msg;
          const bToast = new bootstrap.Toast(toastEl);
          bToast.show();
          return;
        }
      } catch (e) {
        // fall back
      }
      // fallback simple text display
      toastEl.textContent = msg;
      toastEl.classList.remove('d-none', 'hidden');
      clearTimeout(toastEl._t);
      toastEl._t = setTimeout(() => {
        toastEl.classList.add('d-none', 'hidden');
      }, ms);
    }

    // populate selects (defensive)
    function populateUniversitySelects() {
      if (pUniversity) pUniversity.innerHTML = '';
      if (buyerUniversity) buyerUniversity.innerHTML = '<option value="">All participating schools</option>';
      sampleUniversities.forEach(u => {
        const val = u;
        if (pUniversity) {
          const o = document.createElement('option'); o.value = val; o.textContent = u; pUniversity.appendChild(o);
        }
        if (buyerUniversity) {
          const ob = document.createElement('option'); ob.value = val; ob.textContent = u; buyerUniversity.appendChild(ob);
        }
      });
    }

    // render seller product list
    function renderSellerList() {
      if (!sellerList) return;
      const products = loadProducts();
      if (!products.length) {
        sellerList.innerHTML = '<div class="text-muted small">No products yet.</div>';
        return;
      }
      sellerList.innerHTML = products.map(p => {
        const img = escapeAttr(p.image || 'https://via.placeholder.com/80x80?text=No+Img');
        const fbLink = p.fb ? `<a href="${escapeAttr(p.fb)}" target="_blank" rel="noopener" class="link-primary small">Facebook</a>` : '';
        return `
        <div class="card mb-2 shadow-sm">
          <div class="card-body p-2 d-flex gap-2">
            <img src="${img}" alt="" style="width:72px;height:72px;object-fit:cover;border-radius:.5rem">
            <div class="flex-fill">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="fw-semibold">${escapeHtml(p.name)}</div>
                  <div class="small text-muted">${escapeHtml(p.university)} • ${escapeHtml(p.category)}</div>
                </div>
                <div class="text-danger fw-bold">₱${Number(p.price).toFixed(2)}</div>
              </div>
              <div class="small text-muted mt-2">${escapeHtml(p.desc || '')}</div>
              <div class="mt-2 d-flex gap-2 align-items-center">
                ${fbLink}
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto delete-btn" data-id="${escapeAttr(p.id)}">Delete</button>
              </div>
            </div>
          </div>
        </div>`;
      }).join('');

      // attach delete handlers
      sellerList.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          if (!id) return;
          const list = loadProducts().filter(x => String(x.id) !== String(id));
          saveProducts(list);
          renderSellerList();
          renderBuyerGrid();
          showToast('Product deleted');
        });
      });
    }

    // render buyer grid (search + filter)
    function renderBuyerGrid() {
      if (!buyerGrid) return;
      const products = loadProducts();
      const q = buyerSearch ? (buyerSearch.value || '').trim().toLowerCase() : '';
      const uni = buyerUniversity ? (buyerUniversity.value || '').trim() : '';
      const filtered = products.filter(p => {
        if (uni && p.university !== uni) return false;
        if (!q) return true;
        const hay = `${p.name || ''} ${p.desc || ''} ${p.category || ''}`.toLowerCase();
        return hay.includes(q);
      });
      if (!filtered.length) {
        buyerGrid.innerHTML = '';
        if (buyerEmpty) buyerEmpty.classList.remove('d-none', 'hidden');
        return;
      }
      if (buyerEmpty) buyerEmpty.classList.add('d-none', 'hidden');

      buyerGrid.innerHTML = filtered.map(p => {
        const img = escapeAttr(p.image || 'https://via.placeholder.com/300x200?text=No+Image');
        const fbBtn = p.fb ? `<a href="${escapeAttr(p.fb)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary w-100 mt-2">Visit FB Page</a>` : '';
        const orderBtn = `<button class="btn btn-sm btn-danger ms-auto order-btn" data-id="${escapeAttr(p.id)}">Order</button>`;
        return `
        <div class="col-12 col-sm-6 col-lg-4 mb-3">
          <div class="card h-100 shadow-sm">
            <img src="${img}" class="card-img-top" style="height:160px;object-fit:cover" alt="">
            <div class="card-body d-flex flex-column">
              <h6 class="card-title mb-1">${escapeHtml(p.name)}</h6>
              <div class="small text-muted mb-2">${escapeHtml(p.university)} • ${escapeHtml(p.category)}</div>
              <p class="small text-muted mb-2">${escapeHtml(p.desc || '')}</p>
              <div class="d-flex align-items-center mt-auto">
                <div class="fw-bold">₱${Number(p.price).toFixed(2)}</div>
                <div class="ms-auto" style="min-width:90px">
                  ${fbBtn || orderBtn}
                </div>
              </div>
            </div>
          </div>
        </div>`;
      }).join('');
      // attach order handlers (if you want JS action on order button)
      buyerGrid.querySelectorAll('.order-btn').forEach(b => {
        b.addEventListener('click', () => {
          const id = b.getAttribute('data-id');
          showToast('Order feature not implemented — opens FB page when provided');
          // optional: open FB if exists for that product
          const product = loadProducts().find(p => String(p.id) === String(id));
          if (product && product.fb) window.open(product.fb, '_blank', 'noopener');
        });
      });
    }

    // debounce helper
    function debounce(fn, wait = 200) {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
      };
    }

    // wire up initial UI
    populateUniversitySelects();
    renderSellerList();
    renderBuyerGrid();

    // navigation helpers
    function showScreen(name) {
      if (screenHome) screenHome.classList.add('d-none', 'hidden');
      if (screenBuyer) screenBuyer.classList.add('d-none', 'hidden');
      if (screenSeller) screenSeller.classList.add('d-none', 'hidden');
      const el = qs(name);
      if (el) {
        el.classList.remove('d-none', 'hidden');
      } else {
        console.warn('showScreen: no element with id', name);
      }
    }

    // event bindings (defensive)
    if (btnBuyer) btnBuyer.addEventListener('click', () => { showScreen('screen-buyer'); renderBuyerGrid(); });
    if (btnSeller) btnSeller.addEventListener('click', () => { showScreen('screen-seller'); renderSellerList(); });
    if (buyerBack) buyerBack.addEventListener('click', () => { showScreen('screen-home'); });
    if (sellerBack) sellerBack.addEventListener('click', () => { showScreen('screen-home'); });

    if (buyerSearch) buyerSearch.addEventListener('input', debounce(renderBuyerGrid, 180));
    if (buyerUniversity) buyerUniversity.addEventListener('change', renderBuyerGrid);

    if (sellerForm) {
      sellerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = pName ? (pName.value || '').trim() : '';
        const price = pPrice ? (parseFloat(pPrice.value) || 0) : 0;
        const category = pCategory ? (pCategory.value || '') : '';
        const university = pUniversity ? (pUniversity.value || '') : '';
        if (!name || !university) {
          showToast('Please provide product name and university');
          return;
        }
        const product = {
          id: genId(),
          name, price, category, university,
          fb: pFB ? (pFB.value || '').trim() : '',
          image: pImage ? (pImage.value || '').trim() : '',
          desc: pDesc ? (pDesc.value || '').trim() : ''
        };
        const list = loadProducts();
        list.unshift(product);
        saveProducts(list);
        renderSellerList();
        renderBuyerGrid();
        sellerForm.reset();
        showToast('Product added');
      });
    }

    if (clearSeller && sellerForm) clearSeller.addEventListener('click', () => sellerForm.reset());

    // expose small debug helpers on window (optional)
    window.ubelt = {
      loadProducts, saveProducts, renderBuyerGrid, renderSellerList
    };
  }); // end ready
})(); // end IIFE
