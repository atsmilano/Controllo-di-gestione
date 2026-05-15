
(function(){
  const input = document.getElementById('page-filter');
  if (!input) return;
  input.addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.nav-group li').forEach(li => {
      const a = li.querySelector('a');
      li.style.display = !q || (a && a.dataset.title.includes(q)) ? '' : 'none';
    });
  });
  const here = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-group a').forEach(a => {
    if (a.getAttribute('href') === here) a.classList.add('active');
  });
})();
