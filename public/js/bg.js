const images = [
    '/development/public/images/Bg1.jpg',
    '/development/public/images/Bg2.jpg',
    '/development/public/images/Bg3.jpg',
    '/development/public/images/Bg4.jpg',
    '/development/public/images/Bg5.jpg',
    '/development/public/images/Bg6.jpg',
    '/development/public/images/Bg7.jpg',
    '/development/public/images/Bg8.jpg',
    '/development/public/images/Bg9.jpg'
  ];
  
  let current = 0;
  let showingBg1 = true;
  
  const bg1 = document.getElementById('bg1');
  const bg2 = document.getElementById('bg2');
  
  bg1.style.backgroundImage = `url(${images[current]})`;
  bg1.classList.add('visible');
  
  setInterval(() => {
    current = (current + 1) % images.length;
    const nextImage = images[current];
  
    const incoming = showingBg1 ? bg2 : bg1;
    const outgoing = showingBg1 ? bg1 : bg2;
  
    incoming.style.backgroundImage = `url(${nextImage})`;
    
    incoming.classList.remove('visible');
    void incoming.offsetWidth; 
    incoming.classList.add('visible');
    
    outgoing.classList.remove('visible');
  
    showingBg1 = !showingBg1;
  }, 15000);
  