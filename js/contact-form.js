/**
 * Contact Form Handler
 * Handles form validation and submission for the contact form
 */

// HubSpot Configuration
const HUBSPOT_PORTAL_ID = '5882962';
const HUBSPOT_FORM_GUID = 'b90616df-0d42-47af-9471-4031d807c413'; // À remplacer par le GUID de votre formulaire HubSpot

/**
 * Submit form data to HubSpot
 * @param {Object} formData - Form data object with firstname, lastname, email, company, message
 * @returns {Promise<boolean>} - Success status
 */
async function submitToHubSpot(formData) {
    // Skip if form GUID is not configured
    if (HUBSPOT_FORM_GUID === 'YOUR_FORM_GUID') {
        console.warn('HubSpot Form GUID not configured. Skipping HubSpot submission.');
        return true;
    }

    const hubspotUrl = `https://api.hsforms.com/submissions/v3/integration/submit/${HUBSPOT_PORTAL_ID}/${HUBSPOT_FORM_GUID}`;
    
    const hubspotData = {
        fields: [
            {
                name: 'firstname',
                value: formData.firstname
            },
            {
                name: 'lastname',
                value: formData.lastname
            },
            {
                name: 'email',
                value: formData.email
            },
            {
                name: 'company',
                value: formData.company || ''
            },
            {
                name: 'message',
                value: formData.message || ''
            }
        ],
        context: {
            pageUri: window.location.href,
            pageName: document.title
        }
    };

    try {
        const response = await fetch(hubspotUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(hubspotData)
        });

        if (!response.ok) {
            console.error('HubSpot submission failed:', response.statusText);
            return false;
        }

        console.log('Successfully submitted to HubSpot');
        return true;
    } catch (error) {
        console.error('Error submitting to HubSpot:', error);
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form Validation and Submission
    const contactForm = document.getElementById('contactForm');
    
    if (!contactForm) {
        return; // Form not present on this page
    }

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset errors
        document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
        document.querySelectorAll('input, textarea').forEach(el => el.classList.remove('error'));

        let isValid = true;

        // Validate firstname
        const firstname = document.getElementById('firstname');
        if (firstname.value.trim() === '') {
            document.getElementById('firstnameError').classList.add('show');
            firstname.classList.add('error');
            isValid = false;
        }

        // Validate lastname
        const lastname = document.getElementById('lastname');
        if (lastname.value.trim() === '') {
            document.getElementById('lastnameError').classList.add('show');
            lastname.classList.add('error');
            isValid = false;
        }

        // Validate email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value.trim())) {
            document.getElementById('emailError').classList.add('show');
            email.classList.add('error');
            isValid = false;
        }

        if (isValid) {
            // Disable submit button to prevent double submission
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';

            // Prepare form data
            const formData = new FormData(this);

            try {
                // Prepare data for HubSpot
                const formDataObj = {
                    firstname: formData.get('firstname'),
                    lastname: formData.get('lastname'),
                    email: formData.get('email'),
                    company: formData.get('company'),
                    message: formData.get('message')
                };

                // Submit to HubSpot (non-blocking)
                submitToHubSpot(formDataObj).catch(err => {
                    console.error('HubSpot submission error:', err);
                    // Continue even if HubSpot fails
                });
                
                // Send form data to PHP script
                const response = await fetch('send-email.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message
                    document.getElementById('successMessage').classList.add('show');

                    // Reset form
                    this.reset();

                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        document.getElementById('successMessage').classList.remove('show');
                    }, 5000);
                } else {
                    // Show error
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    });

    // Remove error styling on input
    document.querySelectorAll('input, textarea').forEach(element => {
        element.addEventListener('input', function() {
            this.classList.remove('error');
            const errorId = this.id + 'Error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.remove('show');
            }
        });
    });
});
