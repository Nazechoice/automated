(function(){
  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');

  function normalizeTheme(t) {
    return t === 'light-mode' || t === 'light' ? 'light' : 'dark';
  }

  function setTheme(t){
    const theme = normalizeTheme(t);
    root.setAttribute('data-theme', theme);
    root.classList.toggle('light-mode', theme === 'light');
    document.cookie = 'theme=' + theme + '; path=/; max-age=31536000; samesite=lax';
    try { localStorage.setItem('theme', theme); } catch (e) {}
  }

  const saved = (function(){
    try { return localStorage.getItem('theme'); } catch(e) {}
    const m = document.cookie.match(/theme=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : null;
  })();

  if(saved){ setTheme(saved); }

  if(toggle){
    toggle.addEventListener('click', function(){
      const cur = normalizeTheme(root.getAttribute('data-theme') || (root.classList.contains('light-mode') ? 'light' : 'dark'));
      setTheme(cur === 'dark' ? 'light' : 'dark');
    });
  }
})();

