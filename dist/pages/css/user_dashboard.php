<style>
    :root {
      --primary-color: #6a11cb;
      --secondary-color: #2575fc;
      --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }

    /* Smooth transitions */
    .app-main {
      transition: all 0.3s ease;
    }

    /* Modern alert styling */
    .alert-toast {
      width: 100%;
      max-width: 450px;
    }

    .alert-toast .alert {
      border-radius: 12px;
      border: none;
      transform: translateY(-100%);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* Gradient text for special elements */
    .gradient-text {
      background: var(--gradient);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    /* Improved scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--primary-color);
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--secondary-color);
    }

    /* Smooth body transition */
    body {
      transition: background-color 0.3s ease;
    }

    /* Card styling */
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    /* Button styling */
    .btn-primary {
      background: var(--gradient);
      border: none;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5a0db5 0%, #1a65e8 100%);
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
    }

    /* Form control styling */
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
    }
  </style>
