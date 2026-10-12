/**
 * Mercedes-Benz AMG Style Animation Engine & Interactivity Script
 * ODRA Road Sweepers Sales Portal
 */

document.addEventListener('DOMContentLoaded', () => {
  initCountUp();
  initScrollAnimations();
  initTableInteractivity();
});

/**
 * Animated number count-up effect
 */
function initCountUp() {
  const counterElements = document.querySelectorAll('.amg-counter[data-target]');
  if (!counterElements.length) return;

  counterElements.forEach(el => {
    const target = parseFloat(el.getAttribute('data-target')) || 0;
    const isCurrency = el.getAttribute('data-currency') === 'true';
    const duration = 1200; // 1.2s animation
    const startTime = performance.now();

    function updateCounter(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Smooth cubic ease-out
      const easeOut = 1 - Math.pow(1 - progress, 3);
      const current = target * easeOut;

      if (isCurrency) {
        el.textContent = '$' + Math.floor(current).toLocaleString();
      } else if (Number.isInteger(target)) {
        el.textContent = Math.floor(current).toString();
      } else {
        el.textContent = current.toFixed(1);
      }

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        if (isCurrency) {
          el.textContent = '$' + Math.round(target).toLocaleString();
        } else {
          el.textContent = target.toString();
        }
      }
    }

    requestAnimationFrame(updateCounter);
  });
}

/**
 * Scroll reveal animations using Intersection Observer
 */
function initScrollAnimations() {
  const elements = document.querySelectorAll('.amg-reveal');
  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('amg-slide-up');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  elements.forEach(el => observer.observe(el));
}

/**
 * Table client-side search, status pill filter, and KPI card click filtering
 */
function initTableInteractivity() {
  const searchInput = document.getElementById('quoteTableSearch');
  const tableRows = document.querySelectorAll('#recentQuotesTable tbody tr[data-status]');
  const filterPills = document.querySelectorAll('.quote-filter-pill');
  const countIndicator = document.getElementById('filteredQuotesCount');

  let currentStatus = 'all';
  let currentSearch = '';

  function applyFilters() {
    let visibleCount = 0;

    tableRows.forEach(row => {
      const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
      const rowText = row.textContent.toLowerCase();

      const matchesStatus = (currentStatus === 'all') || (rowStatus === currentStatus);
      const matchesSearch = !currentSearch || rowText.includes(currentSearch);

      if (matchesStatus && matchesSearch) {
        row.style.display = '';
        row.classList.add('amg-fade-in');
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (countIndicator) {
      countIndicator.textContent = `Showing ${visibleCount} of ${tableRows.length} quotes`;
    }

    const noResultsRow = document.getElementById('noQuotesMatchRow');
    if (noResultsRow) {
      noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
    }
  }

  // Search input handler
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      currentSearch = e.target.value.toLowerCase().trim();
      applyFilters();
    });
  }

  // Filter pills handler
  filterPills.forEach(pill => {
    pill.addEventListener('click', () => {
      filterPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      currentStatus = pill.getAttribute('data-filter') || 'all';
      applyFilters();
    });
  });

  // KPI card click-to-filter triggers
  const kpiCards = document.querySelectorAll('.kpi-trigger[data-kpi-filter]');
  kpiCards.forEach(card => {
    card.addEventListener('click', () => {
      const filterTarget = card.getAttribute('data-kpi-filter');
      currentStatus = filterTarget;

      // Update pills
      filterPills.forEach(p => {
        if (p.getAttribute('data-filter') === filterTarget) {
          p.classList.add('active');
        } else {
          p.classList.remove('active');
        }
      });

      applyFilters();

      // Smooth scroll down to the table
      const tableSection = document.getElementById('quotesTableCard');
      if (tableSection) {
        tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/**
 * Quick Quote Inspect Modal Handler
 */
window.openQuickQuoteModal = function(quoteData) {
  const modal = document.getElementById('quickQuoteModal');
  if (!modal) return;

  document.getElementById('modalQuoteNumber').textContent = quoteData.number || 'N/A';
  document.getElementById('modalQuoteStatus').textContent = quoteData.status || 'Draft';
  document.getElementById('modalQuoteStatus').className = 'badge ' + (quoteData.status === 'Finalized' ? 'badge-active' : 'badge-draft');

  document.getElementById('modalQuoteCustomer').textContent = quoteData.customer || 'N/A';
  document.getElementById('modalQuoteContact').textContent = quoteData.contact || 'Direct Purchasing';
  document.getElementById('modalQuoteAddress').textContent = quoteData.address || 'Standard Delivery Location';

  document.getElementById('modalQuoteModel').textContent = quoteData.model || 'ODRA MT4H';
  document.getElementById('modalQuoteChassis').textContent = quoteData.chassis || 'Isuzu NRR Cabover';

  document.getElementById('modalQuoteSubtotal').textContent = quoteData.subtotal ? '$' + parseFloat(quoteData.subtotal).toLocaleString() : '$0';
  document.getElementById('modalQuoteFreight').textContent = quoteData.freight ? '$' + parseFloat(quoteData.freight).toLocaleString() : '$0';
  document.getElementById('modalQuoteTotal').textContent = quoteData.total ? '$' + parseFloat(quoteData.total).toLocaleString() : '$0';

  const viewBtn = document.getElementById('modalQuoteViewBtn');
  if (viewBtn && quoteData.viewUrl) viewBtn.href = quoteData.viewUrl;

  const printBtn = document.getElementById('modalQuotePrintBtn');
  if (printBtn && quoteData.viewUrl) {
    printBtn.href = quoteData.viewUrl + '?print=1';
  }

  const pdfBtn = document.getElementById('modalQuotePdfBtn');
  if (pdfBtn && quoteData.pdfUrl) {
    pdfBtn.href = quoteData.pdfUrl;
    pdfBtn.style.display = quoteData.status === 'Finalized' ? 'inline-flex' : 'none';
  }

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
};

window.closeQuickQuoteModal = function() {
  const modal = document.getElementById('quickQuoteModal');
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
};
