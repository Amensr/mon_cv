<div class="cyber-cv-interactive" id="cv-content">
    <div class="cyber-terminal">
        <div class="terminal-header">
            <div class="terminal-buttons">
                <span class="close"></span>
                <span class="minimize"></span>
                <span class="maximize"></span>
            </div>
            <span class="terminal-title">user@cv-creator: ~/cv</span>
        </div>
        <div class="terminal-body">
            <div class="terminal-line">
                <span class="prompt">$></span>
                <span class="command">cat personal_info.txt</span>
            </div>
            <div class="terminal-output personal-info">
                <div class="info-line">
                    <span class="info-label">NAME:</span>
                    <span class="info-value" contenteditable="true"><?= $personal_info['full_name'] ?></span>
                </div>
                <!-- Autres informations -->
            </div>
            
            <div class="terminal-line">
                <span class="prompt">$></span>
                <span class="command">nano skills.json --edit</span>
            </div>
            <div class="skills-edit">
                <div class="skill-category" data-category="lang">
                    <h3>Programming Languages</h3>
                    <div class="skill-bubbles">
                        <?php foreach ($skills['languages'] as $lang): ?>
                        <div class="skill-bubble" data-level="<?= $lang['level'] ?>">
                            <?= $lang['name'] ?>
                            <div class="skill-level"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Autres catégories -->
            </div>
            
            <div class="hacker-effects">
                <div class="code-rain"></div>
                <div class="scan-line"></div>
            </div>
        </div>
    </div>
    
    <div class="cyber-controls">
        <button class="cyber-btn" id="run-virus-scan">Run Virus Scan</button>
        <button class="cyber-btn" id="encrypt-cv">Encrypt CV</button>
        <button class="cyber-btn" id="hack-mode">Hack Mode</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la vue 3D
    const cv3d = new CV3DViewer('3d-cv-container');
    
    // Effet de scan de virus
    document.getElementById('run-virus-scan').addEventListener('click', function() {
        const terminalBody = document.querySelector('.terminal-body');
        const scanBar = document.createElement('div');
        scanBar.className = 'virus-scan';
        terminalBody.appendChild(scanBar);
        
        gsap.to(scanBar, {
            y: terminalBody.offsetHeight,
            duration: 3,
            onComplete: () => {
                const scanResult = document.createElement('div');
                scanResult.className = 'scan-result';
                scanResult.textContent = 'Scan complete: 0 threats detected';
                terminalBody.appendChild(scanResult);
                
                setTimeout(() => {
                    scanResult.remove();
                }, 3000);
            }
        });
        
        setTimeout(() => {
            scanBar.remove();
        }, 3500);
    });
    
    // Mode hack
    document.getElementById('hack-mode').addEventListener('click', function() {
        document.querySelector('.cyber-cv-interactive').classList.toggle('hack-mode');
        cv3d.explode();
        
        if (document.querySelector('.cyber-cv-interactive').classList.contains('hack-mode')) {
            // Générer du code qui défile aléatoirement
            const codeRain = document.querySelector('.code-rain');
            const chars = "01ABCDEFGHIJKLMNOPQRSTUVWXYZ$%&/#[]";
            
            for (let i = 0; i < 50; i++) {
                const codeLine = document.createElement('div');
                codeLine.className = 'code-line';
                
                let codeText = '';
                for (let j = 0; j < 30; j++) {
                    codeText += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                
                codeLine.textContent = codeText;
                codeLine.style.left = `${Math.random() * 100}%`;
                codeLine.style.animationDelay = `${Math.random() * 5}s`;
                
                codeRain.appendChild(codeLine);
            }
        }
    });
    
    // Éditeur de compétences interactif
    document.querySelectorAll('.skill-bubble').forEach(bubble => {
        const level = bubble.getAttribute('data-level');
        const levelBar = bubble.querySelector('.skill-level');
        
        levelBar.style.width = `${level}%`;
        
        bubble.addEventListener('click', function() {
            const newLevel = prompt("Enter new skill level (0-100):", level);
            if (newLevel >= 0 && newLevel <= 100) {
                levelBar.style.width = `${newLevel}%`;
                bubble.setAttribute('data-level', newLevel);
                
                // Animation de mise à jour
                gsap.fromTo(levelBar, 
                    { backgroundColor: '#ff0' },
                    { backgroundColor: '#0f0', duration: 0.5 }
                );
            }
        });
    });
});
</script>