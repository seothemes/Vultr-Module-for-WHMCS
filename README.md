# Vultr-Module-for-WHMCS

## Installation

Upload the modules folder

Visit Setup>Addon Modules

Click activate

Click configure

Enter your license & API key

Tick Full Administrator

Click Save

Go to Addons > Vultr

Create a Non-Windows option group

Create a Windows option group

{Note:} if you receive a cURL error, tick the disable_ssl option and save changes

Go to Setup>Products/Services>Products/Services

Create a new product

[Product Type:] Dedicated/VPS Server

Continue

Details Tab

[Welcome Email:] Dedicated/VPS Server Welcome Email

Module Settings Tab

[Module Name:] Vultr

Choose a plan

Choose a startup script if you have any

Custom Fields Tab

[Field Name:] server_id

[Field Type:] Text Box

Admin Only

Configurable Options Tab

Choose the group you crated

{Note:} Windows/Non-Windows will depend on the plan you have chosen

Save Changes



##Customization

You can edit the clientarea.tpl file as required
