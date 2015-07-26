\name{concerto.test.run}
\alias{concerto.test.run}
\title{
Running Concerto test
}
\description{
Runs Concerto test.
}
\usage{
concerto.test.run(testID, params = list(), workspaceID = concerto$workspaceID)
}
\arguments{
  \item{testID}{
Test ID or name.
}
  \item{params}{
Named list of parameters you want to pass to the test logic.
}
  \item{workspaceID}{
Concerto workspace ID where the test object is located.
}
}
\details{
}
\value{
Named list with the test specific return variables.
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
test <- concerto.test.run(1)
}
\keyword{ test }
