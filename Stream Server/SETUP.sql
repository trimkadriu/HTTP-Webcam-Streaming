
--
-- Database: `wc_stream`
--
CREATE DATABASE IF NOT EXISTS `wc_stream` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `wc_stream`;

-- --------------------------------------------------------

--
-- Table structure for table `auth_users`
--

CREATE TABLE IF NOT EXISTS `auth_users` (
  `auth_id` varchar(24) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `stream_speed` int(4) NOT NULL,
  `last_alive` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE IF NOT EXISTS `data` (
`id` int(10) unsigned NOT NULL,
  `auth_id` varchar(24) NOT NULL,
  `image` longblob NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=1713 DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_users`
--
ALTER TABLE `auth_users`
 ADD PRIMARY KEY (`auth_id`);

--
-- Indexes for table `data`
--
ALTER TABLE `data`
 ADD PRIMARY KEY (`id`), ADD KEY `auth_id` (`auth_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1713;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `data`
--
ALTER TABLE `data`
ADD CONSTRAINT `user_auth_id` FOREIGN KEY (`auth_id`) REFERENCES `auth_users` (`auth_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
