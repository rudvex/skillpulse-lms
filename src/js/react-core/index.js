import './blocks';
import './admin/editor';
import './admin/pages';
import './admin'; // Import admin UI component styles (ListView, DetailView).

// Shared admin UI system for react-core.
import './styles/index.scss';

// Initialize Toast Container.
import { ToastContainer } from './components/Toast';
import React from 'react';
import { createRoot } from 'react-dom/client';

// Create toast container.
const toastContainer = document.createElement('div');
toastContainer.id = 'splms-toast-container';
document.body.appendChild(toastContainer);

const root = createRoot(toastContainer);
root.render(<ToastContainer />);