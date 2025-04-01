<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h2>Success!</h2>
        <p id="successModalMessage">You have successfully registered a new customer.</p>
        <button onclick="registerAgain()">Register Again</button>
        <button onclick="goToDashboard()">Go Back to Dashboard</button>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal">
    <div class="modal-content">
        <h2>Error</h2>
        <p id="errorModalMessage">Something went wrong. Please try again.</p>
        <button onclick="tryAgain()">Try Again</button>
        <button onclick="goToDashboard()">Go Back to Dashboard</button>
    </div>
</div>