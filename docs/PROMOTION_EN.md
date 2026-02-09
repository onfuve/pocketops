# Pocket Business — Product Overview

**A lightweight, professional business management application for Iranian businesses**

---

## Introduction

**Pocket Business** is a web-based software system that brings together all the daily needs of a small or medium business in one place: from contact and invoice management to lead generation, products and services, price lists, calendar, and tasks. The Persian user interface, mobile-first design, and logical interconnection of components make it easy to use anytime, anywhere. Every section links to related sections; leads connect to contacts and invoices, products connect to price lists and invoices, and tasks connect to contacts, leads, or invoices — all accessible with one or two clicks.

---

## Outstanding Features

### Contact Management
- **Business Address Book**: Register customers, suppliers, and business partners
- **Complete Profile**: Name, phone, email, address, and notes
- **Address Label Printing**: Ready to stick on shipments
- **Receive & Pay**: Record financial transactions with each contact
- **CSV Import/Export**: Bulk import contacts from Excel/CSV files, export for backup

### Invoices
- **Quick Invoice Issuance**: Create invoices with Shamsi (Jalali) calendar dates
- **Invoice Printing**: Clean, professional printouts for customers
- **Payment Options**: Define payment methods (cash, check, card, etc.)
- **Payment Recording**: Mark invoices as paid
- **Attachments**: Add file attachments to invoices

### Leads
- **Lead Capture**: Record leads from calls, chats, and communications
- **Status Management**: Manage sales pipeline (new, contacted, qualified, converted, etc.)
- **Convert to Contact**: Convert lead to contact with one click
- **Create Invoice from Lead**: Generate invoice directly from lead
- **Comments & Attachments**: Add notes and files for each lead

### Products & Services
- **Product Catalog**: Define goods and services with name, description, suggested price, and unit
- **Tagging**: Organize with tags for quick search and filtering
- **CSV Import**: Bulk import products from file (name column is sufficient)
- **Smart Search**: Auto-suggestions in invoice form based on product name

### Price Lists
- **Custom Price Lists**: Create multiple price lists for different products
- **Sectioning**: Group items into different sections
- **Public Link**: Share list with customers without login required
- **Display Templates**: Simple, with photos, or grid layout
- **Duplicate List**: Quickly copy a list for a new version

### Product Landing Pages
- **Dedicated Product Page**: Each product can have its own landing page
- **Call-to-Action (CTA)**: Purchase, call, WhatsApp, or custom link
- **Public Link**: Share with customers without system login

### Calendar & Tasks
- **Shamsi Calendar**: Display events and reminders based on Jalali calendar
- **Reminders**: Set reminders for important dates
- **Tasks**: Define tasks, assign to contact or lead, and change status

### Settings
- **Company Settings**: Company name, logo, and information
- **Payment Methods**: Define payment methods (bank account, cash, check, etc.)
- **Lead Channels**: Define lead sources (website, Instagram, phone call, etc.)
- **Tags**: Manage custom tags
- **CSV Import**: Import contacts and products from files

---

## Component Connections & Workflow

All components of Pocket Business are interconnected; each object links to related objects, and you can navigate to the desired section with one or two clicks.

### Contact, the Central Hub

**Contact** (customer, supplier, or business partner) is the starting point. From each contact's page, you can:
- Issue a sales invoice or purchase receipt (one click)
- Print address label
- Record receive & pay transactions
- View list of invoices and financial transactions for that contact

### From Lead to Contact and Invoice

**Lead** has direct connections to contact and invoice:
- You can convert a lead to a **contact** — information transfers to the address book
- After conversion, the "View Contact" link takes you directly to the contact profile
- With one click, you can create a **draft invoice** from the lead — contact and initial items auto-fill
- If the lead has a referrer, you can navigate to the referrer's contact page

### Invoice and Contact

Every **invoice** is linked to a contact. The contact's balance (debt/credit) updates based on invoices and payments. From the invoice page, you can always navigate to the contact page and vice versa.

### Product, Price List, and Invoice

- **Product** is the foundation for price lists and invoice items
- **Price list** is built from products; each item can link to a product or have custom name and price
- **Product landing page** can be created for each product with an independent public link
- When issuing an **invoice**, smart product search provides suggestions — selecting a product fills description and price

### Tasks, Everywhere

**Task** can be linked to a contact, lead, or invoice. From the task page, one click takes you to that contact, lead, or invoice. From the lead page, you can also create a calendar task.

### Tags, Everywhere

**Tags** can be used on contacts, leads, invoices, and products. Used for categorization and search across the entire system.

---

## Ease of Navigation

### Categorized Menu

The main menu is grouped by function:
- **Core**: Dashboard, Contacts, Invoices, Leads
- **Planning**: Calendar, Tasks
- **Products & Sales**: Products & Services, Price Lists, Landing Pages
- **Settings**: Tags, CSV Import, Lead Channels, Company Settings
- **Actions**: New Contact, Logout

On desktop, groups are separated with subtle dividers; on mobile, each group displays under its own heading.

### Dashboard; Starting Point of Each Day

The dashboard shows a summary of the day's status and makes all important items accessible in one place:
- Statistics cards: Number of contacts, leads, open tasks, unpaid invoices
- Today: Today's reminders (with links to lead or calendar)
- Upcoming: Reminders and invoice due dates for the next 7 days
- Tasks needing attention
- Active leads
- Overdue invoices

Each item is clickable and takes you directly to the relevant page (lead, invoice, task, calendar).

### Quick Movement Between Pages

On every page, action buttons are available:
- Contact page: Issue sales/purchase invoice, receive & pay, transactions, print label
- Lead page: Convert to contact, issue invoice, change status, add task to calendar
- Invoice page: Print, record payment, edit

Back and "List" links are also present on all pages so the return path is clear.

---

## Why Pocket Business?

| Feature | Description |
|---------|-------------|
| **100% Persian** | Persian user interface, Shamsi calendar, and Persian digits |
| **Mobile-First** | Easy use on mobile and tablet |
| **Right-to-Left (RTL)** | Design optimized for Persian language |
| **Lightweight & Fast** | No unnecessary complexity, focus on daily tasks |
| **Ready to Scale** | MySQL foundation and modular architecture for future growth |

---

## Technology & Architecture

- **Backend**: PHP 8.2+ with Laravel framework  
- **Frontend**: Responsive design, Vazirmatn font  
- **Database**: MySQL with multi-user support  
- **Calendar**: Shamsi (Jalali), Tehran timezone  

---

## Summary

Pocket Business is a tool for business owners and sales teams who want to manage contacts, invoices, leads, and products in a Persian, simple environment — without high costs and without the complexity of heavy enterprise software.

**Everything in your pocket.**
