document.addEventListener('DOMContentLoaded', () => {
    // Utility functions

    const openModal = (title, content) => {
        const modal = document.getElementById('app-modal')
        const modalTitle = document.getElementById('modal-title')
        const modalBody = document.getElementById('modal-body')

        modalTitle.innerHTML = title
        modalBody.innerHTML = content
        modal.style.display = 'flex'
    }

    const closeModal = () => {
        const modal = document.getElementById('app-modal')
        modal.style.display = 'none'
    }

    // Attach close event listeners
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-btn')) {
            closeModal()
        }
        if (e.target.id === 'app-modal') {
            closeModal()
        }
    })

    // AJAX helper function
    const makeAjaxRequest = (action, data) => {
        return new Promise((resolve, reject) => {
            const formData = new FormData()
            formData.append('action', 'pet_shop_action')
            formData.append('pet_action', action)
            formData.append('nonce', pet_shop_ajax.nonce)

            for (const key in data) {
                formData.append(key, data[key])
            }

            fetch(pet_shop_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resolve(data.data)
                    } else {
                        reject(data.data)
                    }
                })
                .catch(error => reject(error))
        })
    }



    // Product form HTML




})