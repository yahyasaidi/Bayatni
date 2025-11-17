document.addEventListener("DOMContentLoaded", () => {
  // Select all functionality
  const selectAllCheckbox = document.getElementById("select-all")
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", () => {
      const checkboxes = document.querySelectorAll(".booking-select")
      checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked
      })
    })
  }

  // Bulk actions
  const bulkActionSelect = document.getElementById("bulk-action")
  const applyBtn = document.querySelector(".apply-btn")

  if (applyBtn) {
    applyBtn.addEventListener("click", () => {
      const selectedAction = bulkActionSelect.value
      if (!selectedAction) {
        alert("Please select an action")
        return
      }

      const selectedBookings = Array.from(document.querySelectorAll(".booking-select:checked")).map((checkbox) =>
        checkbox.closest("tr").querySelector("td:nth-child(2)").textContent.replace("#BK-", ""),
      )

      if (selectedBookings.length === 0) {
        alert("Please select at least one booking")
        return
      }

      // Confirm before proceeding
      if (selectedAction === "delete") {
        if (!confirm(`Are you sure you want to delete ${selectedBookings.length} bookings?`)) {
          return
        }
      } else if (selectedAction === "cancel") {
        if (!confirm(`Are you sure you want to cancel ${selectedBookings.length} bookings?`)) {
          return
        }
      }

      // Process the action
      processBookingBulkAction(selectedAction, selectedBookings)
    })
  }

  function processBookingBulkAction(action, bookingIds) {
    // Here you would typically send an AJAX request to a server endpoint
    console.log(`Processing ${action} for bookings:`, bookingIds)

    // Example implementation
    const formData = new FormData()
    formData.append("action", action)
    formData.append("booking_ids", JSON.stringify(bookingIds))

    fetch("process_booking_bulk_action.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert(data.message)
          // Reload the page to reflect changes
          window.location.reload()
        } else {
          alert("Error: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("An error occurred while processing your request")
      })
  }
})
