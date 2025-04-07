<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content sh-lg">
      <div class="modal-header">
        <img src="./assets/img/success.png" alt="successImg">
        <h2>Success!</h2>
        <p id="successModalMessage">You have successfully registered a new customer.</p>
      </div>      
      <div class="btn-container">
        <button onclick="registerAgain()" id="regAgain" class="btn btn-sm btn-outline sh-sm">Register Again</button>
        <button onclick="goToDashboard()" id="goDashboard" class="btn btn-sm btn-secondary sh-sm">Go Back to Dashboard</button>
      </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal">
    <div class="modal-content sh-lg">
      <div class="modal-header">
      <img src="./assets/img/error.png" alt="errorImg">
        <h2>Error</h2>
        <p id="errorModalMessage">Something went wrong.</p>
      </div>
      <div class="btn-container">
        <button onclick="tryAgain()" id="tryAgain" class="btn btn-sm btn-outline sh-sm">Try Again</button>
        <button onclick="goToDashboard()" id="goDashboard" class="btn btn-sm btn-secondary sh-sm">Go Back to Dashboard</button>
      </div>
    </div>
</div>