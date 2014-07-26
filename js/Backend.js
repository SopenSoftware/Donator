Ext.ns('Tine.Donator');

/**
* sopen member backend
*/
Tine.Donator.fundMasterBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'FundMaster',
   recordClass: Tine.Donator.Model.FundMaster
});

Tine.Donator.donationBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'Donation',
   recordClass: Tine.Donator.Model.Donation
});

Tine.Donator.campaignBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'Campaign',
   recordClass: Tine.Donator.Model.Campaign
});

Tine.Donator.projectBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'Project',
   recordClass: Tine.Donator.Model.Project
});

Tine.Donator.donationAccountBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'DonationAccount',
   recordClass: Tine.Donator.Model.DonationAccount
});

Tine.Donator.donationUnitBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'DonationUnit',
   recordClass: Tine.Donator.Model.DonationUnit
});

Tine.Donator.regularDonationBackend = new Tine.Tinebase.data.RecordProxy({
   appName: 'Donator',
   modelName: 'RegularDonation',
   recordClass: Tine.Donator.Model.RegularDonation
});