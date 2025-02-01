// CouponModal.js
const CouponModal = ({ isOpen, onClose, targetUrl, coupon }) => {
    const [inputCoupon, setInputCoupon] = React.useState('');
    const [error, setError] = React.useState('');
  
    if (!isOpen) return null;
  
    const handleSubmit = (e) => {
      e.preventDefault();
      if (inputCoupon.trim().toLowerCase() === coupon.toLowerCase()) {
        window.open(targetUrl, '_blank');
        onClose();
      } else {
        setError('Cupón inválido. Por favor, intente nuevamente.');
      }
    };
  
    return React.createElement('div', {
      className: 'modal d-block',
      style: { backgroundColor: 'rgba(0,0,0,0.5)' }
    },
      React.createElement('div', {
        className: 'modal-dialog modal-dialog-centered'
      },
        React.createElement('div', {
          className: 'modal-content'
        },
          React.createElement('div', {
            className: 'modal-header'
          },
            React.createElement('h5', {
              className: 'modal-title'
            }, 'Ingrese el cupón'),
            React.createElement('button', {
              type: 'button',
              className: 'btn-close',
              onClick: onClose
            })
          ),
          React.createElement('div', {
            className: 'modal-body'
          },
            React.createElement('form', {
              onSubmit: handleSubmit
            },
              React.createElement('div', {
                className: 'mb-3'
              },
                React.createElement('input', {
                  type: 'text',
                  value: inputCoupon,
                  onChange: (e) => setInputCoupon(e.target.value),
                  className: 'form-control',
                  placeholder: 'Ingrese el código del cupón'
                })
              ),
              error && React.createElement('div', {
                className: 'alert alert-danger'
              }, error),
              React.createElement('div', {
                className: 'modal-footer'
              },
                React.createElement('button', {
                  type: 'button',
                  className: 'btn btn-secondary',
                  onClick: onClose
                }, 'Cancelar'),
                React.createElement('button', {
                  type: 'submit',
                  className: 'btn btn-primary'
                }, 'Verificar')
              )
            )
          )
        )
      )
    );
  };