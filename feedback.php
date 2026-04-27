<?php
$page_title = 'Feedback & Rating';
$active_page = 'feedback';
require_once __DIR__ . '/../layouts/header.php';

$routes_stmt = $pdo->prepare("SELECT id, route_name FROM routes ORDER BY route_name ASC");
$routes_stmt->execute();
$routes = $routes_stmt->fetchAll();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $route_id = !empty($_POST['route_id']) ? $_POST['route_id'] : null;
    $rating = (int)$_POST['rating'];
    $comments = clean($_POST['comments']);
    
    $stmt_insert = $pdo->prepare("INSERT INTO feedback (user_id, route_id, rating, comments) VALUES (?, ?, ?, ?)");
    if ($stmt_insert->execute([$_SESSION['user_id'], $route_id, $rating, $comments])) {
        $msg = "Thank you for your valuable feedback! This helps us improve MUET transport.";
    }
}
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1000px; margin: 0 auto;">
    <!-- Feedback Form -->
    <div class="card mb-4" style="border-top: 4px solid var(--primary);">
        <div class="card-header">
            <h3 style="font-weight: 700; margin: 0;">📝 Submit Review</h3>
        </div>
        <div class="content-body" style="padding: 2rem;">
            <?php if ($msg): ?>
                <div style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid var(--success);">
                    ✅ <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Rate Your Experience <span style="color: var(--danger);">*</span></label>
                    <div style="display: flex; gap: 10px; font-size: 2rem; justify-content: center; margin-bottom: 1rem; direction: rtl;" class="rating-stars">
                        <input type="radio" name="rating" id="star5" value="5" required style="display:none;">
                        <label for="star5" style="cursor:pointer; color:#CBD5E1; transition:color 0.2s;">★</label>
                        
                        <input type="radio" name="rating" id="star4" value="4" style="display:none;">
                        <label for="star4" style="cursor:pointer; color:#CBD5E1; transition:color 0.2s;">★</label>
                        
                        <input type="radio" name="rating" id="star3" value="3" style="display:none;">
                        <label for="star3" style="cursor:pointer; color:#CBD5E1; transition:color 0.2s;">★</label>
                        
                        <input type="radio" name="rating" id="star2" value="2" style="display:none;">
                        <label for="star2" style="cursor:pointer; color:#CBD5E1; transition:color 0.2s;">★</label>
                        
                        <input type="radio" name="rating" id="star1" value="1" style="display:none;">
                        <label for="star1" style="cursor:pointer; color:#CBD5E1; transition:color 0.2s;">★</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Which Route? (Optional)</label>
                    <select name="route_id" class="form-control">
                        <option value="">-- General Service Feedback --</option>
                        <?php foreach ($routes as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->route_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Comments <span style="color: var(--danger);">*</span></label>
                    <textarea name="comments" class="form-control" required rows="5" placeholder="Tell us what you liked or how we can improve..."></textarea>
                </div>
                
                <button type="submit" name="submit_feedback" class="btn btn-primary" style="width: 100%; font-weight: 700; padding: 0.75rem 1.5rem;">Submit Feedback</button>
            </form>
        </div>
    </div>
    
    <!-- Guidelines Panel -->
    <div>
        <div class="card" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); height: 100%;">
            <div class="content-body" style="padding: 2.5rem;">
                <span style="font-size: 3rem; display: block; margin-bottom: 1rem;">🗣️</span>
                <h3 style="font-weight: 800; color: var(--primary); margin-bottom: 1rem;">Your Voice Matters</h3>
                <p style="color: var(--dark); line-height: 1.6; margin-bottom: 1.5rem;">
                    The transport office regularly monitors feedback to identify delayed routes, problematic schedules, and driver performance.
                </p>
                <ul style="color: var(--gray); padding-left: 1.2rem; line-height: 1.8; font-size: 0.875rem;">
                    <li>Keep comments constructive and specific.</li>
                    <li>If reporting an incident, please select the specific Route.</li>
                    <li>5 Stars indicates excellent punctuality.</li>
                    <li>1 Star indicates severe delays or poor service.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS for rating stars logic */
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #EAB308 !important;
}
.rating-stars input:checked ~ label {
    color: #EAB308 !important;
}
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
