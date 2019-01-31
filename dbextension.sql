-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2019 at 11:30 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 5.6.39

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbextension`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblfile`
--

CREATE TABLE `tblfile` (
  `file_id` int(11) NOT NULL,
  `file_directory` varchar(255) NOT NULL,
  `transaction_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tblnotification`
--

CREATE TABLE `tblnotification` (
  `notification_id` int(11) NOT NULL,
  `notif_type_id` int(11) NOT NULL,
  `notification_sender` varchar(20) NOT NULL,
  `notification_receiver` varchar(20) NOT NULL,
  `notification_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tblnotif_type`
--

CREATE TABLE `tblnotif_type` (
  `notif_type_id` int(11) NOT NULL,
  `notif_desc` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblnotif_type`
--

INSERT INTO `tblnotif_type` (`notif_type_id`, `notif_desc`) VALUES
(1, 'Proposal Submission'),
(2, 'Proposal Acceptance'),
(3, 'Proposal Returned'),
(4, 'MOA Notarized');

-- --------------------------------------------------------

--
-- Table structure for table `tblproject_proposals`
--

CREATE TABLE `tblproject_proposals` (
  `proposal_id` int(11) NOT NULL,
  `proposal_title` varchar(255) NOT NULL,
  `proposal_beneficiaries` varchar(255) NOT NULL,
  `proposal_bene_gender` varchar(255) NOT NULL,
  `proposal_directory` varchar(255) NOT NULL,
  `moa_directory` varchar(255) NOT NULL,
  `cover_directory` varchar(255) NOT NULL,
  `proposal_status` int(11) NOT NULL,
  `proposal_date_start` varchar(255) NOT NULL,
  `proposal_date_end` varchar(255) NOT NULL,
  `proposal_partner` varchar(255) NOT NULL,
  `proposal_venue` varchar(255) NOT NULL,
  `proponents` varchar(255) NOT NULL,
  `accreditation_level` varchar(255) NOT NULL,
  `budget_ustp` float NOT NULL,
  `budget_partner` float NOT NULL,
  `total_hours` int(5) NOT NULL,
  `type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `persons_trained` int(5) NOT NULL,
  `rate_satisfactory` int(3) NOT NULL,
  `rate_v_satisfactory` int(3) NOT NULL,
  `rate_excellent` int(3) NOT NULL,
  `days_conducted` int(2) NOT NULL,
  `accomplishment_report` int(1) NOT NULL,
  `comment` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tblproposal_status`
--

CREATE TABLE `tblproposal_status` (
  `proposal_status_id` int(1) NOT NULL,
  `proposal_status_desc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblproposal_status`
--

INSERT INTO `tblproposal_status` (`proposal_status_id`, `proposal_status_desc`) VALUES
(0, 'Pending'),
(1, 'Accepted'),
(2, 'Waiting MOA'),
(3, 'Waiting for Report'),
(4, 'Approved'),
(5, 'Complete');

-- --------------------------------------------------------

--
-- Table structure for table `tbltrans_log`
--

CREATE TABLE `tbltrans_log` (
  `log_id` int(11) NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `log_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbltrans_type`
--

CREATE TABLE `tbltrans_type` (
  `type_id` int(11) NOT NULL,
  `type_desc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbltrans_type`
--

INSERT INTO `tbltrans_type` (`type_id`, `type_desc`) VALUES
(1, 'Proposal Submission'),
(2, 'Report Submission');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `user_school_id` varchar(20) NOT NULL,
  `user_pass` varchar(20) NOT NULL,
  `user_type` varchar(1) NOT NULL,
  `approved` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`user_school_id`, `user_pass`, `user_type`, `approved`) VALUES
('2015101200', '123', '0', 1),
('2015101205', '321', '2', 1),
('2015101206', '321', '2', 2),
('2015101207', '321', '2', 0),
('2015101208', '321', '2', 0),
('2015101209', '321', '2', 0),
('2015101242', '123', '2', 1),
('2015101246', '123', '1', 1),
('3040389', 'extension', '2', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbluserinfo`
--

CREATE TABLE `tbluserinfo` (
  `ui_id` int(11) NOT NULL,
  `ui_school_id` varchar(20) NOT NULL,
  `ui_Fname` varchar(30) NOT NULL,
  `ui_Mname` varchar(30) NOT NULL,
  `ui_Lname` varchar(30) NOT NULL,
  `ui_college` varchar(50) NOT NULL,
  `ui_dept` varchar(50) NOT NULL,
  `ui_position` varchar(20) NOT NULL,
  `ui_gender` varchar(20) NOT NULL,
  `ui_birthday` varchar(20) NOT NULL,
  `ui_age` varchar(5) NOT NULL,
  `ui_email` varchar(30) NOT NULL,
  `ui_contact_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbluserinfo`
--

INSERT INTO `tbluserinfo` (`ui_id`, `ui_school_id`, `ui_Fname`, `ui_Mname`, `ui_Lname`, `ui_college`, `ui_dept`, `ui_position`, `ui_gender`, `ui_birthday`, `ui_age`, `ui_email`, `ui_contact_number`) VALUES
(3, '2015101246', '', '', '', 'CITC', 'IT', 'Dean', '', '', '', 'jhbabia1998@gmail.com', ''),
(4, '2015101242', 'Quinto', 'Auxillo', 'Tan', 'CITC', 'IT', 'Dean', '', '', '', 'delacruzkitryan@gmail.com', ''),
(5, '2015101200', '', '', '', 'CEA', 'Mechanical Engineering', 'Chairperson', '', '', '', 'email@email.com', ''),
(7, '2015101205', 'Kit Ryan', 'Betancor', 'dela Cruz', 'CITC', 'IT', 'Project Leader', 'M', '2019-01-19T16:00:00.', '', 'delacruzkitryan@gmail.com', '09985684995'),
(8, '2015101206', 'Ervyn', 'Lorenzo', 'Montero', 'CEA', 'ARCH', 'Designer', 'M', '2019-01-20T16:00:00.', '', '206@gmail.com', '09062451145'),
(9, '2015101207', 'Kiven', 'Liso', 'Ranan', 'CSM', 'Applied Mathematics', 'Calculator', 'M', '2019-01-21T16:00:00.', '', '207@gmail.com', '09062555456'),
(10, '2015101208', 'Ian', 'Gabriel', 'Noble', 'COT', 'AMT', 'Builder', 'M', '2019-01-22T16:00:00.', '', '208@gmail.com', '09062333214'),
(11, '2015101209', 'Ralph', 'Quinto', 'Tan', 'CSTE', 'BEEd-SpEd', 'sign languager', 'M', '2019-01-23T16:00:00.', '', '209@gmail.com', '09063699987'),
(12, '3040389', 'Maria Teresa', 'Mabaylan', 'Fajardo', 'CSTE', 'SciED', 'project leader', 'F', '1971-04-06T16:00:00.', '', 'mariateresa.fajardo@ustp.edu.p', '09173026633');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `event_id` int(11) NOT NULL,
  `start` varchar(255) NOT NULL,
  `end` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `color` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_file`
--

CREATE TABLE `tbl_file` (
  `proposal_id` int(1) NOT NULL,
  `proposal_directory` varchar(225) NOT NULL,
  `moa_directory` varchar(225) NOT NULL,
  `cover_directory` varchar(225) NOT NULL,
  `attendance_directory` varchar(225) NOT NULL,
  `evaluation` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblfile`
--
ALTER TABLE `tblfile`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `tblnotification`
--
ALTER TABLE `tblnotification`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `tblnotif_type`
--
ALTER TABLE `tblnotif_type`
  ADD PRIMARY KEY (`notif_type_id`);

--
-- Indexes for table `tblproject_proposals`
--
ALTER TABLE `tblproject_proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `proposal_status` (`proposal_status`);

--
-- Indexes for table `tblproposal_status`
--
ALTER TABLE `tblproposal_status`
  ADD PRIMARY KEY (`proposal_status_id`);

--
-- Indexes for table `tbltrans_log`
--
ALTER TABLE `tbltrans_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbltrans_type`
--
ALTER TABLE `tbltrans_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`user_school_id`);

--
-- Indexes for table `tbluserinfo`
--
ALTER TABLE `tbluserinfo`
  ADD PRIMARY KEY (`ui_id`),
  ADD KEY `ui_school_id` (`ui_school_id`);

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `tbl_file`
--
ALTER TABLE `tbl_file`
  ADD KEY `proposal_id` (`proposal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblfile`
--
ALTER TABLE `tblfile`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblnotification`
--
ALTER TABLE `tblnotification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblnotif_type`
--
ALTER TABLE `tblnotif_type`
  MODIFY `notif_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblproject_proposals`
--
ALTER TABLE `tblproject_proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbltrans_log`
--
ALTER TABLE `tbltrans_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbltrans_type`
--
ALTER TABLE `tbltrans_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbluserinfo`
--
ALTER TABLE `tbluserinfo`
  MODIFY `ui_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
