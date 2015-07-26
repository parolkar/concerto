\name{concerto.qti.responseProcessing}
\alias{concerto.qti.responseProcessing}
\title{
QTI response processing
}
\description{
Processes QTI response.
}
\usage{
concerto.qti.responseProcessing(qtiID, ini, userResponse, workspaceID = concerto$workspaceID)
}

\arguments{
  \item{qtiID}{
QTI object ID or name.
}
  \item{ini}{
Result of QTI initialization.
}
  \item{userResponse}{
Test participant response.
}
  \item{workspaceID}{
Concerto workspace ID where the QTI object is located.
}
}
\details{
}
\value{
Named list with elements declared in QTI XML.
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
qti_init <- concerto.qti.initialize(1)
user_response <- concerto.template.show(2)
rp <- concerto.qti.responseProcessing(1,qti_init,user_response)
}

\keyword{ QTI }
