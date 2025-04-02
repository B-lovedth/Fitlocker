<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <img src="./assets/img/success.png" alt="successImg">
        <h2 style="color: green; font-size: 2.5rem;">Success!</h2>
        <p id="successModalMessage">You have successfully registered a new customer.</p>
      </div>      
      <button onclick="registerAgain()" id="regAgain">Register Again</button>
      <button onclick="goToDashboard()" id="goDashboard">Go Back to Dashboard</button>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
      <img src="./assets/img/error.png" alt="errorImg">
        <h2 style="color: red; font-size: 2.5rem;">Error</h2>
        <p id="errorModalMessage">Something went wrong.</p>
      </div>
      <button onclick="tryAgain()" id="tryAgain">Try Again</button>
      <button onclick="goToDashboard()" id="goDashboard">Go Back to Dashboard</button>
    </div>
</div>