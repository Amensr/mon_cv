class CV3DViewer {
  constructor(elementId) {
    this.container = document.getElementById(elementId);
    this.scene = new THREE.Scene();
    this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    this.renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    this.cvMesh = null;
    
    this.init();
    this.animate();
  }
  
  init() {
    // Configuration du renderer
    this.renderer.setSize(this.container.offsetWidth, this.container.offsetHeight);
    this.renderer.setPixelRatio(window.devicePixelRatio);
    this.container.appendChild(this.renderer.domElement);
    
    // Lumière
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    this.scene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(1, 1, 1);
    this.scene.add(directionalLight);
    
    // Chargement du modèle 3D du CV
    this.createCVModel();
    
    // Position de la caméra
    this.camera.position.z = 5;
    
    // Contrôles orbitaux
    this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;
    this.controls.dampingFactor = 0.05;
    
    // Gestion du redimensionnement
    window.addEventListener('resize', this.onWindowResize.bind(this));
    
    // Interaction tactile
    this.setupTouchEvents();
  }
  
  createCVModel() {
    // Création d'un CV en 3D avec texture dynamique
    const geometry = new THREE.BoxGeometry(3, 4, 0.1);
    const textureLoader = new THREE.TextureLoader();
    
    // Créer une texture dynamique à partir du DOM
    const cvElement = document.getElementById('cv-content');
    const html2canvas = window.html2canvas;
    
    html2canvas(cvElement).then(canvas => {
      const texture = new THREE.CanvasTexture(canvas);
      const material = new THREE.MeshPhongMaterial({ 
        map: texture,
        side: THREE.DoubleSide,
        specular: 0x111111,
        shininess: 30
      });
      
      this.cvMesh = new THREE.Mesh(geometry, material);
      this.scene.add(this.cvMesh);
      
      // Animation d'entrée
      gsap.from(this.cvMesh.scale, { 
        x: 0, y: 0, z: 0, 
        duration: 1.5, 
        ease: "elastic.out(1, 0.5)" 
      });
    });
  }
  
  animate() {
    requestAnimationFrame(this.animate.bind(this));
    this.controls.update();
    this.renderer.render(this.scene, this.camera);
    
    // Animation subtile
    if (this.cvMesh) {
      this.cvMesh.rotation.y += 0.001;
    }
  }
  
  onWindowResize() {
    this.camera.aspect = this.container.offsetWidth / this.container.offsetHeight;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(this.container.offsetWidth, this.container.offsetHeight);
  }
  
  setupTouchEvents() {
    let isDragging = false;
    let previousTouchPosition = { x: 0, y: 0 };
    
    this.container.addEventListener('touchstart', (e) => {
      isDragging = true;
      previousTouchPosition = {
        x: e.touches[0].clientX,
        y: e.touches[0].clientY
      };
    }, { passive: true });
    
    this.container.addEventListener('touchmove', (e) => {
      if (!isDragging || !this.cvMesh) return;
      
      const touch = e.touches[0];
      const movementX = touch.clientX - previousTouchPosition.x;
      const movementY = touch.clientY - previousTouchPosition.y;
      
      this.cvMesh.rotation.y += movementX * 0.01;
      this.cvMesh.rotation.x += movementY * 0.01;
      
      previousTouchPosition = {
        x: touch.clientX,
        y: touch.clientY
      };
    }, { passive: true });
    
    this.container.addEventListener('touchend', () => {
      isDragging = false;
    }, { passive: true });
  }
  
  explode() {
    if (!this.cvMesh) return;
    
    // Créer des particules à partir du CV
    const particlesGeometry = new THREE.BufferGeometry();
    const count = 500;
    
    const positions = new Float32Array(count * 3);
    const colors = new Float32Array(count * 3);
    
    for (let i = 0; i < count * 3; i++) {
      positions[i] = (Math.random() - 0.5) * 10;
      colors[i] = Math.random();
    }
    
    particlesGeometry.setAttribute(
      'position',
      new THREE.BufferAttribute(positions, 3)
    );
    
    particlesGeometry.setAttribute(
      'color',
      new THREE.BufferAttribute(colors, 3)
    );
    
    const particlesMaterial = new THREE.PointsMaterial({
      size: 0.1,
      vertexColors: true,
      transparent: true,
      opacity: 0.8
    });
    
    const particles = new THREE.Points(particlesGeometry, particlesMaterial);
    this.scene.add(particles);
    
    // Animation d'explosion
    gsap.to(particles.geometry.attributes.position.array, {
      duration: 2,
      ease: "power4.out",
      onUpdate: () => {
        particles.geometry.attributes.position.needsUpdate = true;
      }
    });
    
    // Réassemblage après 2 secondes
    setTimeout(() => {
      this.reassemble();
      this.scene.remove(particles);
    }, 2000);
  }
  
  reassemble() {
    if (!this.cvMesh) return;
    
    gsap.from(this.cvMesh.scale, {
      x: 0,
      y: 0,
      z: 0,
      duration: 1.5,
      ease: "back.out(4)"
    });
  }
}