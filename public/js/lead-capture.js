/**
 * Quick Lead Capture & Progressive Profiling
 */

document.addEventListener("DOMContentLoaded", function () {
  const quickLeadForm = document.getElementById("quickLeadForm");
  const quickLeadMessage = document.getElementById("quickLeadMessage");

  if (quickLeadForm) {
    quickLeadForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      // Disable button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Processing...';

      try {
        const response = await fetch("/quick-lead", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();

        if (result.status === "success") {
          quickLeadMessage.className =
            "mt-2 small text-white fw-bold bg-success px-2 py-1 rounded";
          quickLeadMessage.textContent = result.message;

          // Trigger Progressive Modal if new lead
          if (result.is_new) {
            setTimeout(() => {
              showProgressiveModal(result.lead_id);
            }, 1500);
          }

          this.reset();
        } else {
          quickLeadMessage.className =
            "mt-2 small text-white fw-bold bg-danger px-2 py-1 rounded";
          quickLeadMessage.textContent = result.message;
        }
      } catch (error) {
        console.error("Error:", error);
        quickLeadMessage.className =
          "mt-2 small text-white fw-bold bg-danger px-2 py-1 rounded";
        quickLeadMessage.textContent =
          "Something went wrong. Please try again.";
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }
});

function showProgressiveModal(leadId) {
  // Check if modal exists, if not create it
  let modal = document.getElementById("progressiveProfileModal");
  if (!modal) {
    createProgressiveModal();
    modal = document.getElementById("progressiveProfileModal");
  }

  // Set lead ID
  const leadIdInput = document.getElementById("progressive_lead_id");
  if (leadIdInput) leadIdInput.value = leadId;

  // Show modal
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

function createProgressiveModal() {
  const modalHtml = `
    <div class="modal fade" id="progressiveProfileModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Complete Your Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Help us serve you better by providing a few more details. This helps us find the best properties for you.</p>
                    <form id="progressiveProfileForm">
                        <input type="hidden" id="progressive_lead_id" name="lead_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">I am interested in:</label>
                            <select name="requirement_type" class="form-select form-select-sm">
                                <option value="buy">Buying Property</option>
                                <option value="sell">Selling Property</option>
                                <option value="rent">Renting</option>
                                <option value="invest">Investment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="name@example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pincode (Auto-fill Location)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="prog_pincode" name="pincode" class="form-control" maxlength="6" placeholder="Enter 6-digit Pincode">
                                <button class="btn btn-outline-secondary" type="button" id="btn_check_pincode"><i class="fas fa-search"></i></button>
                            </div>
                            <small id="pincode_feedback" class="text-muted d-none"></small>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">City</label>
                                <input type="text" id="prog_city" name="city" class="form-control form-control-sm" placeholder="City" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">State</label>
                                <input type="text" id="prog_state" name="state" class="form-control form-control-sm" placeholder="State" readonly>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>`;

  document.body.insertAdjacentHTML("beforeend", modalHtml);

  // Pincode Logic
  const pincodeInput = document.getElementById("prog_pincode");
  const checkBtn = document.getElementById("btn_check_pincode");

  const checkPincode = async () => {
    const pincode = pincodeInput.value;
    const feedback = document.getElementById("pincode_feedback");

    if (pincode.length === 6) {
      feedback.className = "text-info d-block small mt-1";
      feedback.textContent = "Fetching location...";

      if (typeof LocationBankHelper !== "undefined") {
        LocationBankHelper.lookupPincode(
          pincode,
          (data) => {
            document.getElementById("prog_city").value = data.city;
            document.getElementById("prog_state").value = data.state;
            feedback.className = "text-success d-block small mt-1";
            feedback.textContent = "Location found!";
          },
          (error) => {
            feedback.className = "text-danger d-block small mt-1";
            feedback.textContent = error;
            document.getElementById("prog_city").value = "";
            document.getElementById("prog_state").value = "";
          }
        );
      } else {
        console.error("LocationBankHelper not found");
        feedback.textContent = "Location service unavailable";
      }
    }
  };

  pincodeInput.addEventListener("blur", checkPincode);
  checkBtn.addEventListener("click", checkPincode);

  // Form Submit
  document
    .getElementById("progressiveProfileForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');

      submitBtn.disabled = true;
      submitBtn.textContent = "Updating...";

      try {
        const response = await fetch("/update-lead", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();

        if (result.status === "success") {
          const modalEl = document.getElementById("progressiveProfileModal");
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal.hide();

          // Show a toast or alert
          const alertDiv = document.createElement("div");
          alertDiv.className =
            "alert alert-success position-fixed top-0 end-0 m-3 shadow-lg";
          alertDiv.style.zIndex = "9999";
          alertDiv.innerHTML =
            '<i class="fas fa-check-circle me-2"></i> Thank you! Your profile has been updated.';
          document.body.appendChild(alertDiv);

          setTimeout(() => alertDiv.remove(), 3000);
        } else {
          alert("Error: " + result.message);
        }
      } catch (error) {
        console.error("Error updating profile:", error);
        alert("Something went wrong.");
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Update Profile";
      }
    });
}
