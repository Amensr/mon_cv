<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

check_auth();

$plans = get_premium_plans($conn);
$current_plan = get_user_subscription($conn, $_SESSION['user_id']);
?>

<div class="upgrade-container">
    <h1>Passer à Premium</h1>
    <p class="subtitle">Débloquez toutes les fonctionnalités avancées</p>
    
    <?php if ($current_plan && $current_plan['is_active']): ?>
    <div class="current-plan">
        <h3>Votre abonnement actuel</h3>
        <div class="plan-card active">
            <h4><?= htmlspecialchars($current_plan['name']) ?></h4>
            <p>Valide jusqu'au <?= format_date($current_plan['ends_at']) ?></p>
            <ul class="plan-features">
                <?php foreach ($current_plan['features'] as $feature): ?>
                <li><?= htmlspecialchars($feature) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
        <div class="plan-card <?= $plan['recommended'] ? 'recommended' : '' ?>">
            <?php if ($plan['recommended']): ?>
            <div class="recommended-badge">Recommandé</div>
            <?php endif; ?>
            
            <h3><?= htmlspecialchars($plan['name']) ?></h3>
            <div class="plan-price">
                <?= number_format($plan['price'], 2) ?> €
                <span>/ <?= $plan['duration_months'] ?> mois</span>
            </div>
            
            <ul class="plan-features">
                <?php foreach ($plan['features'] as $feature): ?>
                <li><?= htmlspecialchars($feature) ?></li>
                <?php endforeach; ?>
            </ul>
            
            <button class="btn btn-primary" 
                    data-plan="<?= $plan['id'] ?>" 
                    data-price="<?= $plan['price'] ?>">
                Choisir ce plan
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="payment-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Paiement sécurisé</h2>
        <form id="payment-form">
            <input type="hidden" id="plan-id" name="plan_id">
            
            <div class="form-group">
                <label for="card-number">Numéro de carte</label>
                <div id="card-number" class="stripe-element"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="card-expiry">Date d'expiration</label>
                    <div id="card-expiry" class="stripe-element"></div>
                </div>
                
                <div class="form-group">
                    <label for="card-cvc">CVC</label>
                    <div id="card-cvc" class="stripe-element"></div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submit-payment">
                Payer <span id="payment-amount"></span> €
            </button>
            
            <div id="payment-errors" class="alert alert-danger hidden"></div>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('votre_clé_publique_stripe');
    const elements = stripe.elements();
    
    const cardNumber = elements.create('cardNumber');
    cardNumber.mount('#card-number');
    
    const cardExpiry = elements.create('cardExpiry');
    cardExpiry.mount('#card-expiry');
    
    const cardCvc = elements.create('cardCvc');
    cardCvc.mount('#card-cvc');
    
    // Gestion des boutons de plan
    document.querySelectorAll('.plan-card button').forEach(btn => {
        btn.addEventListener('click', function() {
            const planId = this.getAttribute('data-plan');
            const price = this.getAttribute('data-price');
            
            document.getElementById('plan-id').value = planId;
            document.getElementById('payment-amount').textContent = price;
            
            document.getElementById('payment-modal').style.display = 'block';
        });
    });
    
    // Fermer le modal
    document.querySelector('.close-modal').addEventListener('click', function() {
        document.getElementById('payment-modal').style.display = 'none';
    });
    
    // Soumission du formulaire de paiement
    document.getElementById('payment-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-payment');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Traitement...';
        
        const {error, paymentMethod} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
            billing_details: {
                email: '<?= $_SESSION['email'] ?>'
            }
        });
        
        if (error) {
            document.getElementById('payment-errors').textContent = error.message;
            document.getElementById('payment-errors').classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Payer ' + document.getElementById('payment-amount').textContent + ' €';
            return;
        }
        
        // Envoyer le paymentMethod.id à votre serveur
        const formData = new FormData();
        formData.append('plan_id', document.getElementById('plan-id').value);
        formData.append('payment_method_id', paymentMethod.id);
        
        try {
            const response = await fetch('/api/payment.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '/payment_success.php?session_id=' + result.session_id;
            } else {
                throw new Error(result.message || 'Erreur de paiement');
            }
        } catch (err) {
            document.getElementById('payment-errors').textContent = err.message;
            document.getElementById('payment-errors').classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Payer ' + document.getElementById('payment-amount').textContent + ' €';
        }
    });
});
</script>

<style>
.upgrade-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.upgrade-container h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 2rem;
    font-size: 1.2rem;
}

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.plan-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.plan-card.recommended {
    border: 2px solid var(--primary-color);
}

.recommended-badge {
    position: absolute;
    top: -10px;
    right: 20px;
    background: var(--primary-color);
    color: white;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.plan-card h3 {
    color: var(--dark-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.plan-price {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.plan-price span {
    font-size: 1rem;
    color: #666;
    font-weight: normal;
}

.plan-features {
    list-style: none;
    margin-bottom: 2rem;
}

.plan-features li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.plan-features li:last-child {
    border-bottom: none;
}

.current-plan {
    margin-bottom: 3rem;
    text-align: center;
}

.current-plan .plan-card {
    max-width: 400px;
    margin: 0 auto;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
}

.stripe-element {
    padding: 0.8rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-row .form-group {
    flex: 1;
}

.hidden {
    display: none;
}
</style>

<?php require_once 'includes/footer.php'; ?>