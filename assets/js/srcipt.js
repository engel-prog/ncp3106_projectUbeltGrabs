// Basic UI wiring for Ubelt Grabs
// Handles: role selection, back buttons, populating university selects,
// seller product CRUD (localStorage), buyer rendering + search/filter, and toasts

(function(){
  const qs = id => document.getElementById(id);

  // quick sanity-check: warn and stop if critical elements are missing
  const requiredIds = ['screen-home','screen-buyer','screen-seller','btnBuyer','btnSeller','buyer-back','seller-back','buyerUniversity','buyerSearch','buyerGrid','buyerEmpty','pUniversity','sellerForm','pName','pPrice','pCategory','pFB','pImage','pDesc','sellerList','clearSeller','toast'];
  const missing = requiredIds.filter(id => !qs(id));
  if(missing.length){
    console.warn('Ubelt script: missing DOM elements, aborting initialization:', missing);
    return;
  }

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

  // participating universities provided by the user
  const sampleUniversities = [
    'UST',
    'NU',
    'UE',
    'FEU',
    'San Beda',
    'CEU'
  ];

  function showScreen(name){
    screenHome.classList.add('hidden');
    screenBuyer.classList.add('hidden');
    screenSeller.classList.add('hidden');
    const el = qs(name);
    if(el) el.classList.remove('hidden');
  }

  function saveProducts(list){
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list || []));
  }
  function loadProducts(){
    try{
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    }catch(e){
      return [];
    }
  }

  function populateUniversitySelects(){
    // seller pUniversity
    pUniversity.innerHTML = '';
    buyerUniversity.innerHTML = '<option value="">All participating schools</option>';
    sampleUniversities.forEach(u=>{
      const o = document.createElement('option'); o.value = u; o.textContent = u; pUniversity.appendChild(o);
      const ob = document.createElement('option'); ob.value = u; ob.textContent = u; buyerUniversity.appendChild(ob);
    });
  }

  function renderSellerList(){
    const products = loadProducts();
    if(!sellerList) return;
    sellerList.innerHTML = products.map(p=>{
      return `
        <div class="p-3 border rounded-lg bg-gray-50">
          <div class="flex items-start gap-3">
            <img src="${p.image||'https://via.placeholder.com/80x80?text=No+Img'}" alt="" class="w-20 h-20 rounded-lg object-cover">
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-semibold">${escapeHtml(p.name)}</div>
                  <div class="text-xs text-gray-500">${escapeHtml(p.university)} • ${escapeHtml(p.category)}</div>
                </div>
                <div class="text-red-600 font-bold">₱${Number(p.price).toFixed(2)}</div>
              </div>
              <p class="mt-2 text-sm text-gray-700">${escapeHtml(p.desc||'')}</p>
              <div class="mt-2 flex items-center gap-2">
                ${p.fb?`<a href="${escapeAttr(p.fb)}" target="_blank" class="text-xs text-blue-600 underline">Facebook</a>`:''}
                <button data-id="${p.id}" class="delete-btn ml-auto text-xs text-red-600">Delete</button>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('') || '<div class="text-sm text-gray-500">No products yet.</div>';

    // attach delete handlers
    sellerList.querySelectorAll('.delete-btn').forEach(btn=>{
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const list = loadProducts().filter(x=>String(x.id)!==String(id));
        saveProducts(list);
        renderSellerList();
        showToast('Product deleted');
      });
    });
  }

  function renderBuyerGrid(){
    const products = loadProducts();
    const q = (buyerSearch.value||'').trim().toLowerCase();
    const uni = (buyerUniversity.value||'').trim();
    const filtered = products.filter(p=>{
      if(uni && p.university !== uni) return false;
      if(!q) return true;
      return (p.name||'').toLowerCase().includes(q) || (p.desc||'').toLowerCase().includes(q) || (p.category||'').toLowerCase().includes(q);
    });
    buyerGrid.innerHTML = filtered.map(p=>{
      return `
        <div class="p-3 bg-white border rounded-lg shadow-sm">
          <img src="${p.image||'https://via.placeholder.com/300x200?text=No+Image'}" alt="" class="w-full h-36 object-cover rounded-md mb-2">
          <div class="flex items-start justify-between">
            <div>
              <div class="font-semibold">${escapeHtml(p.name)}</div>
              <div class="text-xs text-gray-500">${escapeHtml(p.university)} • ${escapeHtml(p.category)}</div>
            </div>
            <div class="text-red-600 font-bold">₱${Number(p.price).toFixed(2)}</div>
          </div>
          <p class="mt-2 text-sm text-gray-700">${escapeHtml(p.desc||'')}</p>
          <div class="mt-3 flex items-center gap-2">
            ${p.fb?`<a href="${escapeAttr(p.fb)}" target="_blank" class="text-xs text-blue-600 underline">Facebook</a>`:''}
            <button class="ml-auto px-3 py-1 bg-red-600 text-white rounded-md text-sm">Order</button>
          </div>
        </div>
      `;
    }).join('');

    if(filtered.length===0){
      buyerEmpty.classList.remove('hidden');
    }else{
      buyerEmpty.classList.add('hidden');
    }
  }

  function showToast(msg, ms=2500){
    if(!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.remove('hidden');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(()=>{ toastEl.classList.add('hidden'); }, ms);
  }

  function escapeHtml(s){
    if(!s) return '';
    return String(s).replace(/[&<>"']/g, function(c){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }
  function escapeAttr(s){ if(!s) return ''; return s.replace(/"/g,'&quot;'); }

  // initial wiring
  populateUniversitySelects();
  renderSellerList();
  renderBuyerGrid();

  btnBuyer.addEventListener('click', ()=>{ showScreen('screen-buyer'); renderBuyerGrid(); });
  btnSeller.addEventListener('click', ()=>{ showScreen('screen-seller'); renderSellerList(); });
  buyerBack.addEventListener('click', ()=>{ showScreen('screen-home'); });
  sellerBack.addEventListener('click', ()=>{ showScreen('screen-home'); });

  buyerSearch.addEventListener('input', ()=> renderBuyerGrid());
  buyerUniversity.addEventListener('change', ()=> renderBuyerGrid());

  sellerForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const name = (pName.value||'').trim();
    const price = parseFloat(pPrice.value) || 0;
    const category = pCategory.value || '';
    const university = pUniversity.value || '';
    if(!name || !university){ showToast('Please provide product name and university'); return; }
    const product = {
      id: Date.now().toString(36),
      name, price, category, university,
      fb: (pFB.value||'').trim(),
      image: (pImage.value||'').trim(),
      desc: (pDesc.value||'').trim()
    };
    const list = loadProducts();
    list.unshift(product);
    saveProducts(list);
    renderSellerList();
    renderBuyerGrid();
    sellerForm.reset();
    showToast('Product added');
  });

  clearSeller.addEventListener('click', ()=>{ sellerForm.reset(); });

})();